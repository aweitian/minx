<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/25
 * Time: 12:43
 */

namespace Aw\Routing\Route;

use Aw\Http\Request;
use Aw\Http\Response;

interface IRoute
{
    /**
     * @param Request $request
     * @param array $middleware
     * @return bool
     */
    public function route(Request $request, array $middleware);

    /**
     * @return Response
     */
    public function getDispatchResult();

    /**
     * @return RouteHook
     */
    public function getRouteHook();
}