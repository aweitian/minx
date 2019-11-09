<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/28
 * Time: 11:38
 */

namespace Aw\Routing\Route;

use Aw\Http\Request;
use Aw\Pipeline;
use Aw\Routing\Matcher\IMatcher;

class Callback extends Route
{
    protected $callback;

    public function __construct(IMatcher $matcher, \Closure $callback)
    {
        $this->matcher = $matcher;
        $this->callback = $callback;
        $this->newHook();
    }


    /**
     * @param Request $request
     * @param array $middleware
     * @return bool
     */
    public function route(Request $request, array $middleware)
    {
        $this->beforeMatch($request, $this->matcher);
        if (!$this->matcher->match($request))
            return false;
        $this->dispatcher = new \Aw\Routing\Dispatch\Callback($this->callback);
        $this->beforeDispatcher($request, $this->matcher, $this->dispatcher);
        if (!$this->dispatcher->detect($request, $this->matcher->getMatchResult())) {
            return false;
        }
        $that = $this;
        $pipe = new Pipeline();
        $this->result = $this->handleResult($pipe->send($request)
            ->through($middleware)
            ->then(function ($request) use ($that) {
                return $that->dispatcher->dispatch();
            }));
        return true;
    }
}