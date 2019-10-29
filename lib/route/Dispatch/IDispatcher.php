<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/25
 * Time: 12:43
 */

namespace Aw\Routing\Dispatch;


use Aw\Http\Request;
use Aw\Http\Response;

interface IDispatcher
{
    /**
     * @param Request $request
     * @param array $matches
     * @return bool
     */
    public function dispatch(Request $request, array $matches);

    /**
     * @return Response
     */
    public function getResponse();
}