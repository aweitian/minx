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
     * @return Response
     */
    public function dispatch();

    /**
     *  @param Request $request
     * @param array $matches
     * @return bool
     */
    public function detect(Request $request, array $matches);
}