<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/8
 * Time: 11:29
 */

namespace Aw\Routing\Route;


use Aw\Http\Request;
use Aw\Http\Response;
use Aw\Routing\Dispatch\IDispatcher;
use Aw\Routing\Matcher\IMatcher;

abstract class Route implements IRoute
{
    /**
     * @var IMatcher
     */
    public $matcher;

    /**
     * @var IDispatcher
     */
    public $dispatcher;
    protected $result;

    /**
     * @var RouteHook
     */
    public $hook;

    public function newHook()
    {
        $this->hook = new RouteHook();
    }

    public function getDispatchResult()
    {
        return $this->result;
    }

    /**
     * 当中间件返回RESPONSE实例,返回为TRUE
     * @param $result
     * @return Response
     */
    protected function handleResult($result)
    {
//        if ($result instanceof Response) {
//            $this->result = $result;
//            return true;
//        }
        return $result;
    }
//
//    /**
//     * @param Request $request
//     * @param array $middleware
//     * @return bool
//     */
//    public function route(Request $request, array $middleware)
//    {
//        $this->beforeMatch($request);
//        if (!$this->matcher->match($request))
//            return false;
//        $this->beforeDispatcher($this->matcher, $request);
//        $that = $this;
//        $pipe = new Pipeline();
//        return $pipe->send($request)
//            ->through($middleware)
//            ->then(function ($request) use ($that) {
//                $f = $that->dispatcher->dispatch($request);
//                $that->result = $that->dispatcher->getResponse();
//                return $f;
//            });
//    }

    public function beforeMatch(Request $request, IMatcher $matcher)
    {
        if (!($this->hook instanceof RouteHook))
            return;
        foreach ($this->hook->getBeforeMatcherHook() as $callback) {
            $callback($request, $matcher);
        }
    }

    public function beforeDispatcher(Request $request, IMatcher $matcher, IDispatcher $dispatcher)
    {
        if (!($this->hook instanceof RouteHook))
            return;
        foreach ($this->hook->getBeforeDispatcherHook() as $callback) {
            $callback($request, $matcher, $dispatcher);
        }
    }

    public function getRouteHook()
    {
        return $this->hook;
    }
}