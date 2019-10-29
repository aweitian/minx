<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/29
 * Time: 9:50
 * @param $name
 * @param $copy_src
 */

//if (!file_exists("cache/filesystem.zip"))
//{
//    system("curl https://codeload.github.com/aweitian/filesystem/zip/master > cache/filesystem.zip");
//}
//system("cd cache && 7z x filesystem.zip filesystem-master/src && cd .. && mkdir lib\\filesystem");
//system("xcopy cache\\filesystem-master\*.* lib\\filesystem /s /e /c /y /h /r");
class sync_3e20f15b38c03d08a00ffb55daa890
{
    public static $map = '';

    public static function init($name, $copy_src)
    {
        $zip_url = "https://codeload.github.com/aweitian/$name/zip/master";
        if (!file_exists("cache/$name.zip")) {
            system("curl $zip_url > cache/$name.zip");
        }
        system("cd cache && 7z -y x $name.zip $name-master/src && cd .. ");
        if ($copy_src) {
            system("mkdir lib\\$name && xcopy cache\\$name-master\src\*.* lib\\$name /s /e /c /y /h /r");
            sync_3e20f15b38c03d08a00ffb55daa890::$map .= '"' . addslashes($copy_src) . '" => ' . '__DIR__ . "/lib/' . $name . '"' . ",\n";
        } else {
            system("xcopy cache\\$name-master\src\*.* lib /s /e /c /y /h /r");
        }
        system("rd /s /Q cache\\$name-master");
    }

    public static function writeMap()
    {
        sync_3e20f15b38c03d08a00ffb55daa890::$map .= '"Aw\\\\" => ' . '__DIR__ . "/lib"';
        file_put_contents("map.php", '<?php return array(' . "\n" . sync_3e20f15b38c03d08a00ffb55daa890::$map . "\n);");
    }
}


//utility
sync_3e20f15b38c03d08a00ffb55daa890::init("utilis", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("view", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("pagination", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("carbon", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("dotnet", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("PinYin", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("code_msg_data", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("code_msg_data", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("captcha", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("config", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("container", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("session", false);
sync_3e20f15b38c03d08a00ffb55daa890::init("pipeline", false);


sync_3e20f15b38c03d08a00ffb55daa890::init("httpclient", "Aw\\Httpclient\\");
sync_3e20f15b38c03d08a00ffb55daa890::init("http", "Aw\\Http\\");
sync_3e20f15b38c03d08a00ffb55daa890::init("route", "Aw\\Routing\\");
sync_3e20f15b38c03d08a00ffb55daa890::init("validator", "Aw\\Validator\\");
sync_3e20f15b38c03d08a00ffb55daa890::init("db-connection", "Aw\\Db\\Connection\\");
sync_3e20f15b38c03d08a00ffb55daa890::init("db-reflection", "Aw\\Db\\Reflection\\");
sync_3e20f15b38c03d08a00ffb55daa890::init("sql-builder", "Aw\\Build\\");
sync_3e20f15b38c03d08a00ffb55daa890::init("sql-auto-gen", "Aw\\Sql\\");
sync_3e20f15b38c03d08a00ffb55daa890::init("cache", "Aw\\Cache\\");
sync_3e20f15b38c03d08a00ffb55daa890::init("filesystem", "Aw\\Filesystem\\");

sync_3e20f15b38c03d08a00ffb55daa890::writeMap();



