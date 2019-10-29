<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16
 * Time: 13:37
 */

namespace Aw;
class Dotnet
{
    protected $path;

    public function __construct($path = '')
    {
        if ($path) {
            $this->path = $path;
        }
    }

    /**
     * 使用字符串，将结果作为函数值返回,
     * TRUE加载到$_ENV环境变量中去
     * @param null $path
     * @return array
     */
    public function load($path = null, $env_mode = false)
    {
        if (is_string($path)) {
            $this->path = $path;
        } else if ($path === true) {
            $env_mode = true;
        }
        $ret = array();
        if (is_string($this->path) && file_exists($this->path)) {
            foreach (file($this->path) as $line) {
                $line = trim($line);
                if ($line == "")
                    continue;
                if ($line[0] == '#')
                    continue;
                $arr = explode("=", $line, 2);
                $key = trim($arr[0]);
                $val = null;
                if (count($arr) == 2) {
                    $val = $this->clean(trim($arr[1]));
                }
                $ret[$key] = $val;
                if ($env_mode) {
                    $_ENV[$key] = $val;
                }
            }
        }
        return $ret;
    }

    protected function clean($line)
    {
        if (!$line) return null;
        if (substr($line, 0, 1) == '"' && substr($line, -1, 1) == '"') {
            return substr($line, 1, -1);
        } else if (substr($line, 0, 1) == "'" && substr($line, -1, 1) == "'") {
            return substr($line, 1, -1);
        }
        return $line;
    }
}