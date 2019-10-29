<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/29
 * Time: 8:48
 */

class AutoLoader
{
    public static function load($files = array())
    {
        foreach ($files as $file) {
            require $file;
        }
    }

    public static function register()
    {
        spl_autoload_register(function ($class) {
            $map = AutoLoader::map();
            foreach ($map as $pre => $dir) {
                if (strpos($class, $pre) === 0) {
                    $post = substr($class, strlen($pre));
                    $path = $dir . "/" . str_replace('\\', '/', $post) . ".php";
                    if (file_exists($path)) {
                        require_once $path;
                        return;
                    }
                    else
                        throw new Exception("$class not found in $path");
                }
            }
        });
    }

    public static function map()
    {
        return include __DIR__ . "/map.php";
    }
}

AutoLoader::register();