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
use Exception;
use ReflectionFunction;

class Callback implements IDispatcher
{
    protected $callback;
    protected $response;
    protected $request;
    protected $matches;

    /**
     * 回调函数第一个参数是request,
     * //     * 第二个参数是matches
     * @param mixed $call
     */
    public function __construct($call)
    {
//        $this->matcher = $matcher;
//        $rc = new \ReflectionFunction()
        $re = new ReflectionFunction($call);
        if ($re->getNumberOfParameters() < 3) {
            $this->callback = function ($request, $matches, $detect) use ($call) {
                if ($detect) return true;
                return $call($request, $matches);
            };
        } else {
            $this->callback = $call;
        }
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function dispatch()
    {
        if ($this->request == null) {
            throw new Exception("detect first");
        }
        $callback = $this->callback;
        $ret = $callback($this->request, $this->matches, false);

        if ($ret instanceof Response) {
            return $ret;
        } else {
            return new Response($ret);
        }
    }

    /**
     * @param Request $request
     * @param array $matches
     * @return bool
     */
    public function detect(Request $request, array $matches = array())
    {
        $callback = $this->callback;
        if (!is_callable($callback)) {
            return false;
        }
        $ret = $callback($request, $matches, true);
        if ($ret === false)
            return false;
        $this->request = $request;
        $this->matches = $matches;
        return true;
    }
}