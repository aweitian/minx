<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/8
 * Time: 14:15
 */

namespace Aw\Routing\Route;


use Closure;

class RouteHook
{
    private $callback_before_matcher = array();
    private $callback_before_dispatcher = array();

    public function addBeforeMatcherHook(Closure $closure)
    {
        $this->callback_before_matcher[] = $closure;
    }


    public function addBeforeDispatcherHook(Closure $closure)
    {
        $this->callback_before_dispatcher[] = $closure;
    }

    public function getBeforeMatcherHook()
    {
        return $this->callback_before_matcher;
    }


    public function getBeforeDispatcherHook()
    {
        return $this->callback_before_dispatcher;
    }

    public function resetBeforeMatcherHook()
    {
        $this->callback_before_matcher = array();
    }


    public function resetBeforeDispatcherHook()
    {
        $this->callback_before_dispatcher = array();
    }

    public function setBeforeMatcherHook(array $callbacks)
    {
        $this->resetBeforeMatcherHook();
        foreach ($callbacks as $callback) {
            $this->addBeforeMatcherHook($callback);
        }
    }


    public function setBeforeDispatcherHook(array $callbacks)
    {
        $this->resetBeforeDispatcherHook();
        foreach ($callbacks as $callback) {
            $this->addBeforeDispatcherHook($callback);
        }
    }
}