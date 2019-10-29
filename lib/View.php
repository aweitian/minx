<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 8:54
 *
 */

namespace Aw;


use InvalidArgumentException;

class View
{
    protected $ext_path = '';
    protected $viewFile = '';
    protected $inc_File = '';

    protected $variable_scope = array();
    /**
     * All of the finished, captured sections.
     *
     * @var array
     */
    protected $sections = array();

    /**
     * 可以用于SECTION 中 嵌套 SECTION
     * 用于END SECTION时知道是哪个SECTION
     * The stack of in-progress sections.
     *
     * @var array
     */
    protected $sectionStack = array();

    /***
     * @var array
     */
    protected $yields = array();
    /**
     * The parent placeholder for the request.
     *
     * @var array
     */
    protected static $parentPlaceholder = array();

    protected $tpl_dir;

    protected $data = array();

    public $response = '';

    public function __construct($tpl_dir)
    {
        $this->tpl_dir = $tpl_dir;
    }

    public function addVariableScope($key, $val)
    {
        $this->variable_scope[$key] = $val;
    }

    public function mergeVariableScope(array $vars)
    {
        $this->variable_scope = array_merge($this->variable_scope, $vars);
    }

    public function getVariableScope()
    {
        return $this->variable_scope;
    }

    /**
     * PATH可以是index/index or index  or index.php
     * @param $path
     * @param bool $isAbsPath
     */
    public function extend($path, $isAbsPath = false)
    {
        $path = $this->fixPath($path, $isAbsPath);
        $this->ext_path = $path;
        if (file_exists($this->ext_path)) {
            extract($this->data);
            extract($this->variable_scope);
            ob_start();
            include $this->ext_path;

            $this->response = ob_get_clean();
        } else {
            echo "<!-- {$this->ext_path} is nonexist -->";
        }
    }

    /**
     * @param $path
     * @param bool $isAbsPath
     */
    public function includeFile($path, $isAbsPath = false)
    {
        $this->inc_File = $this->fixPath($path, $isAbsPath);
        if (file_exists($this->inc_File)) {
            extract($this->data);
            extract($this->variable_scope);
            include $this->inc_File;
        }

    }

    /**
     * @param $path
     * @param $isAbsPath
     * @return string
     */
    public function fixPath($path, $isAbsPath)
    {
        if ($isAbsPath) return $path;

        $p = $this->tpl_dir . (substr($this->tpl_dir, -1, 1) == '/' ? '' : '/') . $path;
        if (file_exists($p))
            return $p;
        return $p . (substr($path, -4, 4) == '.php' ? '' : '.php');
    }

    /**
     * Start injecting content into a section.
     *
     * @param  string $section
     * @param  string|null $content
     * @return void
     */
    public function start($section, $content = null)
    {
        if ($content === null) {
            if (ob_start()) {
                $this->sectionStack[] = $section;
            }
        } else {
            $this->extendSection($section, $content);
        }
    }

    /**
     * Stop injecting content into a section.
     *
     * @param  bool $overwrite
     * @return string
     * @throws \InvalidArgumentException
     */
    public function end($overwrite = true)
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->sectionStack);

        if ($overwrite) {
            $this->sections[$last] = ob_get_clean();
        } else {
            $this->extendSection($last, ob_get_clean());
        }

        return $last;
    }

    /**
     * Stop injecting content into a section and append it.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function appendSection()
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->sectionStack);

        if (isset($this->sections[$last])) {
            $this->sections[$last] .= ob_get_clean();
        } else {
            $this->sections[$last] = ob_get_clean();
        }

        return $last;
    }

    /**
     * Append content to a given section.
     *
     * @param  string $section
     * @param  string $content
     * @param bool $prepend
     * @return void
     */
    protected function extendSection($section, $content, $prepend = true)
    {
        if (isset($this->sections[$section])) {
            if ($prepend) {
                $content = $this->sections[$section] . $content;
            } else {
                $content = $content . $this->sections[$section];
            }
        }

        $this->sections[$section] = $content;
    }

    /**
     * Get the string contents of a section.
     *
     * @param  string $section
     * @param  string $default
     */
    public function yieldContent($section, $default = '')
    {
        if (!$this->hasSection($section))
            $this->sections[$section] = $default;
        $this->yields[$section] = static::parentPlaceholder($section);
        print $this->yields[$section];
    }

    /**
     * Get the parent placeholder for the current request.
     *
     * @param  string $section
     * @return string
     */
    public static function parentPlaceholder($section = '')
    {
        if (!isset(static::$parentPlaceholder[$section])) {
            static::$parentPlaceholder[$section] = '##parent-placeholder-' . sha1($section) . '##';
        }

        return static::$parentPlaceholder[$section];
    }

    /**
     * Check if section exists.
     *
     * @param  string $name
     * @return bool
     */
    public function hasSection($name)
    {
        return array_key_exists($name, $this->sections);
    }

    /**
     * Get the contents of a section.
     *
     * @param  string $name
     * @param  string $default
     * @return mixed
     */
    public function getSection($name, $default = null)
    {
        return isset($this->sections[$name]) ? $this->sections[$name] : $default;
    }

    /**
     * Get the entire array of sections.
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Flush all of the sections.
     *
     * @return void
     */
    public function flushSections()
    {
        $this->sections = array();
        $this->sectionStack = array();
    }

    /**
     * PATH可以是index/index or index  or index.php
     * Get the evaluated contents of the object.
     *
     * @param $path
     * @param bool $isAbsPath
     * @return string
     */
    public function render($path, $isAbsPath = false)
    {
        $this->viewFile = $this->fixPath($path, $isAbsPath);
        if (substr($path, 0, 9) === 'string://' || file_exists($this->viewFile)) {
            extract($this->data);
            extract($this->variable_scope);
            ob_start();
            include $this->viewFile;
            $this->response .= ob_get_clean();
        } else {
            return "{$this->viewFile} is not exists";
        }
        $placeholders = array();
        foreach ($this->yields as $key => $value) {
            $placeholders[$value] = $this->sections[$key];
        }
        return strtr($this->response, $placeholders);
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name()
    {
        return $this->viewFile;
    }

    /**
     * Add a piece of data to the view.
     *
     * @param  string|array $key
     * @param  mixed $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        $this->data[$key] = $value;
        return $this;
    }
}