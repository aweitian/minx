<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/29
 * Time: 8:48
 */

class AutoLoader
{
    public static $map = array();
    public static $silent = array();

    public static function load($files = array())
    {
        foreach ($files as $file) {
            require $file;
        }
    }

    public static function add($ns_pre, $path)
    {
        self::$map[$ns_pre] = $path;
    }

    public static function addSilent($ns_pre)
    {
        self::$silent[] = $ns_pre;
    }

    public static function register()
    {
        self::$map = require_once "map.php";
        spl_autoload_register(function ($class) {
            foreach (self::$map as $pre => $dir) {
                if (strpos($class, $pre) === 0) {
                    $post = substr($class, strlen($pre));
                    $path = $dir . "/" . str_replace('\\', '/', $post) . ".php";
                    if (file_exists($path)) {
                        require_once $path;
                        return false;
                    } else {
                        foreach (self::$silent as $pre) {
                            if (strncmp($class, $pre, strlen($pre)) === 0) {
                                return false;
                            }
                        }

                        throw new Exception("$class not found in $path");
                    }
                }
            }
        });
    }
}

AutoLoader::register();
AutoLoader::addSilent("App\\Http\\");
AutoLoader::add("App\\", dirname(__DIR__) . "/App");
