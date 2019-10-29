<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/29
 * Time: 11:38
 */
require_once "AutoLoader.php";

$test = new \Aw\Http\Request("/");
echo $test->getPath();

$curl = new \Aw\Httpclient\Curl();
$curl->get("/");