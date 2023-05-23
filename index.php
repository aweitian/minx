<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/5/11
 * Time: 9:41
 */
require_once __DIR__ . "/lib/AutoLoader.php";

$router = new \Aw\Routing\Router(new \Aw\Http\Request());
$router->get("/", function () {
    return "hello world";
});
$router->post("/api", function (\Aw\Http\Request $request) {
    $data = $request->json();
    $data['taw'] = 'lol';
    $r = new \Aw\Http\Response(\Aw\Cmd::make($data));
    return $r->markAsJson();
});
$router->ca();
$response = $router->run();
$response->send();