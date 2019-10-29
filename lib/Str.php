<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8
 * Time: 10:28
 */

namespace Aw;

class Str
{
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int $length
     * @param int $scope
     * @param bool $skip
     * @return string
     */
    public static function random($length = 16, $scope = 7, $skip = false)
    {
        $d = '0123456789';
        $la = 'abcdefghijklmnopqrstuvwxyz';
        $ua = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $c = '~!@#$%^&*()_+-=/*[]|:?<>';
        $characters = '';
        if ($scope & 1) {
            $characters .= $d;
        }
        if ($scope & 2) {
            $characters .= $la;
        }
        if ($scope & 4) {
            $characters .= $ua;
        }
        if ($scope & 8) {
            $characters .= $c;
        }
        if ($skip) {
            $characters = strtr($characters, array(
                'o' => '',
                'O' => '',
                '0' => '',
                '1' => '',
                'L' => '',
                'l' => ''
            ));
        }
//        var_dump($characters);
        $rand_string = '';
        $charactersLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $rand_string .= $characters[rand(0, $charactersLength - 1)];
        }
        return $rand_string;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }
        return false;
    }
}