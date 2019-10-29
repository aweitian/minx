<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/16
 * Time: 10:18
 */

namespace Aw\Filesystem;


class Filter
{
    /**
     * 递归遍历目录,如果回调函数返回FALSE中断遍历,返回TRUE把当前路径PUSH到结果返回
     * 回调只有一个参数,是路径
     * @param $dir
     * @param \Closure $filter
     * @param array $init
     * @return array
     */
    public static function filterRecursive($dir, \Closure $filter, array &$init = array())
    {
        if (!is_dir($dir)) {
            return $init;
        }
        foreach (glob($dir . "/*") as $v) {
            $ret = $filter($v);
            if ($ret === false) break;
            if ($ret === true) $init[] = $v;
            is_dir($v) && self::filterRecursive($v, $filter, $init);
        }
        return $init;
    }

    /**
     * @param $dir
     * @param \Closure $callback 参数一个,PATH
     * @param Condition $condition
     * @param bool $recursive
     * @return array
     */
    public static function filterCondition($dir, \Closure $callback, Condition $condition = null, $recursive = false)
    {
        if (is_null($condition)) {
            $condition = Condition::create();
        }
        $callback = function ($path) use ($callback, $condition) {
            if ($callback($path)) {
                if ($condition->isModeFilename()) {
                    $cmp = pathinfo($path, PATHINFO_FILENAME);
                } else {
                    $cmp = pathinfo($path, PATHINFO_BASENAME);;
                }
                if (!$condition->isEmptyOnly()) {
                    if ($condition->isInOnly($cmp))
                        return true;
                } elseif (!$condition->isEmptyExcept()) {
                    if (!$condition->isInExcept($cmp))
                        return true;
                } else {
                    return true;
                }
            }
            return null;
        };

        if ($recursive)
            $ret = Filter::filterRecursive($dir, $callback);
        else
            $ret = Filter::filter($dir, $callback);

        if ($condition->isReturnBasename()) {
            return array_map(function ($value) {
                return pathinfo($value, PATHINFO_BASENAME);
            }, $ret);
        }
        if ($condition->isReturnFilename()) {
            return array_map(function ($value) {
                return pathinfo($value, PATHINFO_FILENAME);
            }, $ret);
        }
        return $ret;
    }

    /**
     * @param $dir
     * @param $ext
     * @param Condition $condition
     * @param bool $recursive
     * @return array
     */
    public static function filterEndswith($dir, $ext, $condition = null, $recursive = false)
    {
        return self::filterCondition($dir, function ($path) use ($ext) {
            $len = mb_strlen($ext);
            return mb_substr($path, -1 * $len, $len) == $ext;
        }, $condition, $recursive);
    }

    /**
     * @param $dir
     * @param Condition $condition
     * @param bool $recursive
     * @return array
     */
    public static function getDirectories($dir, $condition = null, $recursive = false)
    {
        return self::filterCondition($dir, function ($path) {
            return is_dir($path);
        }, $condition, $recursive);
    }

    /**
     * @param $dir
     * @param Condition $condition
     * @param bool $recursive
     * @return array
     */
    public static function getFiles($dir, $condition = null, $recursive = false)
    {
        return self::filterCondition($dir, function ($path) {
            return is_file($path) && !is_dir($path);
        }, $condition, $recursive);
    }

    /**
     * @param $dir
     * @param \Closure $filter
     * @return array
     */
    public static function filter($dir, \Closure $filter)
    {
        $result = array();
        if (!is_dir($dir)) {
            return array();
        }
        foreach (glob($dir . "/*") as $v) {
            $ret = $filter($v);
            if ($ret === false) break;
            if ($ret === true) $result[] = $v;
        }
        return $result;
    }

}