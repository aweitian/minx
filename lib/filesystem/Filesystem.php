<?php

/**
 * 2017/5/9 17:36:25
 * 目录操作
 */

namespace Aw\Filesystem;

class Filesystem
{
    /**
     * 获取目录所有文件大小(递归)
     *
     * @param $dir string
     * @return int
     */
    public static function size($dir)
    {
        $s = 0;
        foreach (glob($dir . '/*') as $v) {
            $s += is_file($v) ? filesize($v) : self::size($v);
        }
        return $s;
    }

    /**
     * 删除文件,如果是文件就删除，返回是否删除成功.
     * 是目录不删除返回true
     *
     * @param $file string
     *            文件路径
     * @return bool
     */
    public static function delFile($file)
    {
        if (is_file($file)) {
            return !!unlink($file);
        }
        return true;
    }

    /**
     *
     * @param string $file
     * @return boolean
     */
    public static function touch($file)
    {
        return !!@touch($file);
    }

    /**
     * 递归删除目录
     * 是文件不删除返回true
     *
     * @param $dir
     * @param bool $delself
     * @return bool
     */
    public static function delDir($dir, $delself = true)
    {
        if (!is_dir($dir)) {
            return true;
        }
        foreach (glob($dir . "/*") as $v) {
            is_dir($v) ? self::delDir($v, true) : unlink($v);
        }
        return (!$delself) || ($delself && !!rmdir($dir));
    }

    /**
     * 创建目录
     * 如果目录存在返回
     * 不存在创建
     * 返回目录是否存在
     *
     * @param $dir
     * @param int $auth
     * @param bool $recursive
     * @return bool
     */
    public static function createDir($dir, $recursive = true, $auth = 0755)
    {
        if (!empty ($dir)) {
            return !!is_dir($dir) or !!mkdir($dir, $auth, $recursive);
        }
        return false;
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
    public static function copyDir($old, $new, $copyself = false)
    {
        if (!is_dir($old))
            return false;
        if ($copyself) {
            if (substr($new, -1, 1) !== "/") {
                $new = $new . '/';
            }
            $new = $new . basename($old);
        }
        is_dir($new) or mkdir($new, 0755, true);
        foreach (glob($old . '/*') as $v) {
            $to = $new . '/' . basename($v);
            $f = is_file($v) ? copy($v, $to) : self::copyDir($v, $to, false);
            if (!$f)
                return false;
        }
        return true;
    }

    /**
     * 复制文件
     * 如果不是文件返回false
     *
     * @param string $file 源路径，不存在返回false
     * @param string $to 目录路径，只创建dirname($to)
     * @param bool $force
     * @return bool
     */
    public static function copyFile($file, $to, $force = true)
    {
        if (!is_file($file)) {
            return false;
        }
        // 创建目录
        if (!is_dir(dirname($to))) {
            if ($force) {
                self::createDir(dirname($to));
            } else {
                return false;
            }
        }

        return copy($file, $to);
    }

}