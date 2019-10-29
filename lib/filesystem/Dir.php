<?php

/**
 * 2017/5/9 17:36:25
 * 目录操作
 */

namespace Aw\Filesystem;

class Dir
{
    protected $currentDir = null;

    protected $workspace = null;

    public function __construct($dir)
    {
        if ($this->chdir($dir)) {
            $this->workspace = $dir;
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function cd($name = '')
    {
        if ($name == '') {
            $dir = $this->workspace;
            if (is_null($dir))
                return false;
        } else {
            $dir = $this->currentDir . DIRECTORY_SEPARATOR . $name;
        }
        return $this->chdir($dir);
    }

    /**
     * @return string|null
     *
     */
    public function getcwd()
    {
        $path = str_replace('\\', '/', $this->currentDir);
        return $this->cleanPath($path);
    }

    /**
     * @return mixed|null
     */
    public function workspace()
    {
        if (is_null($this->workspace))
            return null;
        return $this->cleanPath(str_replace('\\', '/', $this->workspace));
    }

    /**
     * @param $path
     * @return mixed
     */
    public function cleanPath($path)
    {
        while (strpos($path, '//') !== false) {
            $path = str_replace('//', '/', $path);
        }
        return $path;
    }

    /**
     * 返回路径分隔符为 /
     */
    public function getRelativePath()
    {
        if (is_null($this->workspace) || is_null($this->currentDir))
            return null;
        $cur = $this->getcwd();
        $wd = $this->workspace();
        return substr($cur, strlen($wd));
    }

    /**
     * @param $dir
     * @return bool
     */
    public function hasDir($dir)
    {
        return !!is_dir($this->currentDir . DIRECTORY_SEPARATOR . $dir);
    }

    /**
     * @param $file
     * @return bool
     */
    public function hasFile($file)
    {
        return !!is_file($this->currentDir . DIRECTORY_SEPARATOR . $file);
    }

    /**
     * @param int $level
     * @return bool
     */
    public function up($level = 1)
    {
        $dir = $this->currentDir;
        if ($level <= 0) return false;
        while ($level--) {
            $dir = dirname($dir);
        }
        return $this->chdir($dir);
    }

    /**
     * @param $name
     * @param int $time The touch time.
     * @param int $atime the access time
     * @return bool
     */
    public function touch($name, $time = null, $atime = null)
    {
        if (is_null($this->currentDir))
            return false;
        return touch($this->currentDir . DIRECTORY_SEPARATOR . $name, $time, $atime);
    }

    /**
     * @param $name
     * @return bool
     */
    public function delFile($name)
    {
        if (is_null($this->currentDir))
            return false;
        return unlink($this->currentDir . DIRECTORY_SEPARATOR . $name);
    }

    /**
     * 复制文件
     * 如果不是文件返回false
     *
     * @param string $file 源路径，不存在返回false
     * @param string $to 目录路径，只创建dirname($to)
     * @return bool
     */
    public function copyFile($file, $to)
    {
        if (is_null($this->currentDir))
            return false;
        return Filesystem::copyFile($this->currentDir . DIRECTORY_SEPARATOR . $file, $this->currentDir . DIRECTORY_SEPARATOR . $to, false);
    }

    /**
     * 复制目录(递归)
     * copyDir("/aa/bb/c","/dd")  把c下所有的文件CP到/DD下
     * copyDir("/aa/bb/c","/dd",true)  把c下所有的文件CP到/DD/c下
     *
     *
     * @param string $old
     * @param string $new
     * @param bool $copyself
     * @return bool
     */
    public function copyDir($old, $new, $copyself = false)
    {
        if (is_null($this->currentDir))
            return false;
        return Filesystem::copyDir($this->currentDir . DIRECTORY_SEPARATOR . $old, $this->currentDir . DIRECTORY_SEPARATOR . $new, $copyself);
    }

    /**
     * @param $dirname
     * @param int $mode
     * @return bool
     */
    public function mkdir($dirname, $mode = 0755)
    {
        if (is_null($this->currentDir))
            return false;
        return mkdir($this->currentDir . DIRECTORY_SEPARATOR . $dirname, $mode);
    }

    /**
     * @param $dirname
     * @return bool
     */
    public function delDir($dirname)
    {
        if (is_null($this->currentDir))
            return false;
        return Filesystem::delDir($this->currentDir . DIRECTORY_SEPARATOR . $dirname);
    }

    /**
     * @param $dir
     * @return bool
     */
    public function chdir($dir)
    {
        if (is_dir($dir)) {
            $this->currentDir = $dir;
            return true;
        }
        return false;
    }
}