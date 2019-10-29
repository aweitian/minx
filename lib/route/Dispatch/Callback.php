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
use Aw\Routing\Matcher\IMatcher;

class Callback implements IDispatcher
{
    protected $callback;
    protected $response;

    /**
     * 回调函数第一个参数是request,
     * //     * 第二个参数是matches
     * @param mixed $call
     */
    public function __construct($call)
    {
//        $this->matcher = $matcher;
        $this->callback = $call;
    }

    public function dispatch(Request $request, array $matches = array())
    {
        $callback = $this->callback;
        if (!is_callable($callback)) {
            return false;
        }
        $ret = $callback($request, $matches);
        if ($ret === false)
            return false;
        if ($ret instanceof Response) {
            $this->response = $ret;
        } else {
            $this->response = new Response($ret);
        }
        return true;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}