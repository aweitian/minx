<?php

/**
 * @date 2017/6/8 17:50:44
 */
namespace Aw;

class Pipeline {
    /**
     * @var array
     */
    protected $middlewares = array();

    protected $request;

    /***
     * @param $request
     * @return $this
     */
    public function send($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param array $middlewares
     * @return $this
     */
    public function through(array $middlewares)
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * @param \Closure $destination
     * @return mixed
     */
    public function then(\Closure $destination)
    {
        $pipes = array_reverse($this->middlewares);
        $run = array_reduce($pipes, function ($carry, $pipe) {
            return function ($passable) use ($carry, $pipe) {
                if ($pipe instanceof \Closure)
                {
                    return call_user_func_array($pipe, array($passable, $carry));
                }
                else
                {
                    return $this->handle($pipe, array($passable, $carry));
                }

            };
        }, function ($passable) use ($destination){
            return call_user_func($destination, $passable);
        });
        return call_user_func($run, $this->request);
    }

    /**
     * @param $class
     * @param $arg
     * @return mixed|null
     */
    protected function handle($class,$arg)
    {
        $rc = new \ReflectionClass($class);
        if ($rc->hasMethod("handle"))
        {
            $ins = $rc->newInstance();
            $met = $rc->getMethod("handle");
            $ret = $met->invoke($ins,$arg[0],$arg[1]);
            return $ret;
        }
        return null;
    }

}