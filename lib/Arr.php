<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8
 * Time: 10:28
 */

namespace Aw;

class Arr
{
    const K_REPLACE = 0;
    const K_START = 1;
    const K_END = 2;

    /**
     * @param $arr
     * @param $key
     * @param mixed $default
     * @return mixed
     */
    public static function get_item($arr, $key, $default = null)
    {
        if (array_key_exists($key, $arr)) {
            return $arr[$key];
        }
        return $default;
    }

    /**
     * array(外键,新KEY,JOIN_ARRAY)
     * merge($ori, array('fk','new_key',$array),...)
     * @return array
     */
    public static function leftJoin()
    {
        $args = func_get_args();

        if (empty($args)) {
            return array();
        }

        $arr = array_shift($args);

        if (empty($args))
            return $arr;
        foreach ($arr as & $item) {
            foreach ($args as $arg) {
                if (!is_array($arg) || count($arg) != 3)
                    continue;
                $key = $arg[0];
                $new = $arg[1];
                $val = $arg[2];
                if (array_key_exists($key, $item)) {
                    $item[$new] = self::get_item($val, $item[$key]);
                }
            }
        }
        return $arr;
    }


    /**
     * 第一个数组，必选
     * 最后一个BOOL，可选
     * 中间至少一个字符串类型
     * @return array
     * @throws \Exception
     */
    public static function get()
    {
        if (func_num_args() < 2) {
            throw new \Exception('less than 2 parameters.');
        }
        $array = func_get_arg(0);
        if (!is_array($array)) {
            throw new \Exception('First arg must be array.');
        }
        $strict = func_get_arg(func_num_args() - 1);
        $rear = 0;
        if (!is_bool($strict)) {
            $strict = true;
        } else {
            $rear = 1;
        }
        $filters = array();
        for ($i = 1; $i < func_num_args() - $rear; $i++) {
            $p = func_get_arg($i);
            if (is_string($p) || is_int($p)) {
                $filters[] = $p;
            }
        }
        if (empty($filters)) {
            throw new \Exception('less than 1 string parameter for filtering.');
        }
        $ret = array();
        foreach ($filters as $filter) {
            if (!array_key_exists($filter, $array)) {
                if ($strict)
                    return array();
            } else {
                $ret[$filter] = $array[$filter];
            }
        }
        return $ret;
    }

    /**
     * 功能和Get函数一样,第二个参数可以是逗号分隔的字符串或者数组
     * @param $array
     * @param array|string $filters
     * @param bool $strict
     * @return array
     * @throws \Exception
     */
    public static function g(array $array, $filters, $strict = false)
    {

        if (!is_array($array)) {
            throw new \Exception('First arg must be array.');
        }

        if (!is_bool($strict)) {
            $strict = true;
        }

        if (is_string($filters)) {
            $filters = explode(",", $filters);
        } else {
            $filters = (array)$filters;
        }

        $ret = array();
        foreach ($filters as $filter) {
            if (!array_key_exists($filter, $array)) {
                if ($strict)
                    return array();
            } else {
                $ret[$filter] = $array[$filter];
            }
        }
        return $ret;
    }

    /**
     * 第一个数组，必选
     * 至少一个字符串类型
     * 比较数组键值
     * @return array
     * @throws \Exception
     */
    public static function except()
    {
        if (func_num_args() < 2) {
            throw new \Exception('less than 2 parameters.');
        }
        $array = func_get_arg(0);
        if (!is_array($array)) {
            throw new \Exception('First arg must be array.');
        }
        $filters = array();
        for ($i = 1; $i < func_num_args(); $i++) {
            $p = func_get_arg($i);
            if (is_string($p) || is_int($p)) {
                $filters[] = $p;
            }
        }
        if (empty($filters)) {
            throw new \Exception('less than 1 string parameter for filtering.');
        }
        $ret = array();
        foreach ($array as $key => $filter) {
            if (in_array($key, $filters)) {
                continue;
            }
            $ret[$key] = $array[$key];
        }
        return $ret;
    }

    /**
     * 对数组键值进行
     * @param $arr
     * @param $find
     * @param $replace
     * @param int $where
     */
    public static function k($arr, $find, $replace = "", $where = self::K_REPLACE)
    {
        $new = array();
        foreach ($arr as $key => $val) {
            if ($where == self::K_START) {
                $nk = Str::startReplace($key, $find, $replace);
            } else if ($where == self::K_END) {
                $nk = Str::endReplace($key, $find, $replace);
            } else {
                $nk = str_replace($find, $replace, $key);
            }
            $new[$nk] = $val;
        }
        return $new;
    }

    /**
     * FILTER为空,默认返回全部数据,当$strict为真时,返回空
     * 比较数组键值
     * @param array $array
     * @param array $filters
     * @param bool $strict
     * @return array
     */
    public static function e(array $array, array $filters, $strict = false)
    {
        if (empty($filters)) {
            if ($strict)
                return array();
            else
                return $array;
        }
        $ret = array();
        foreach ($array as $key => $filter) {
            if (in_array($key, $filters)) {
                continue;
            }
            $ret[$key] = $array[$key];
        }
        return $ret;
    }

    /**
     * 第一个数组，必选
     * 至少一个字符串类型
     * 比较数组内容
     * @return array
     * @throws \Exception
     */
    public static function filter()
    {
        if (func_num_args() < 2) {
            throw new \Exception('less than 2 parameters.');
        }
        $array = func_get_arg(0);
        if (!is_array($array)) {
            throw new \Exception('First arg must be array.');
        }
        $filters = array();
        for ($i = 1; $i < func_num_args(); $i++) {
            $p = func_get_arg($i);
            if (is_string($p) || is_int($p)) {
                $filters[] = $p;
            }
        }
        if (empty($filters)) {
            throw new \Exception('less than 1 string parameter for filtering.');
        }
        $ret = array();
        foreach ($array as $filter) {
            if (in_array($filter, $filters)) {
                continue;
            }
            $ret[] = $filter;
        }
        return $ret;
    }

    /**
     * FILTER为空,默认返回全部数据,当$strict为真时,返回空
     * @param array $array
     * @param array $filters
     * @param bool $strict
     * @return array
     */
    public static function f(array $array, array $filters, $strict = false)
    {
        if (empty($filters)) {
            if ($strict)
                return array();
            else
                return $array;
        }
        $ret = array();
        foreach ($array as $value) {
            if (in_array($value, $filters)) {
                continue;
            }
            $ret[] = $value;
        }
        return $ret;
    }
}