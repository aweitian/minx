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

class AtCall implements IDispatcher
{
    public $callback;
    protected $response;

    /**
     * @param string $call
     * @param string $default_namespace
     * @param string $default_action
     * @throws Exception
     */
    public function __construct($call, $default_namespace = "\\App\\Http", $default_action = "index")
    {
        if (!is_string($call)) {
            throw new Exception("at call only support string");
        }
        $this->callback = $call;
        if (substr($this->callback, 0, 1) !== "\\") {
            $this->callback = $default_namespace . '\\' . $call;
        }
        if (strpos($this->callback, '@') === false) {
            $this->callback = $this->callback . "@" . $default_action;
        }
    }

    /**
     * @param Request $request
     * @param array $matches
     * @return bool
     * @throws Exception
     */
    public function dispatch(Request $request, array $matches = array())
    {
        $t = explode('@', $this->callback);
        if (count($t) != 2) {
            throw new Exception('invalid at call:' . $this->callback);
        }
        $cls = $t[0];
        $act = $t[1];
        if (!class_exists($cls)) {
            $this->response = new Response("class {$cls} is not found", 404);
            return false;
        }
        $rc = new \ReflectionClass($cls);
        if (!$rc->hasMethod($act)) {
            $this->response = new Response("{$act} in {$cls} is not found", 404);
            return false;
        }
        $method = $rc->getMethod($act);
        if (!$method->isPublic()) {
            $this->response = new Response("{$act} in {$cls} should be public", 403);
            return false;
        }

        if ($rc->hasMethod("__construct")) {
            $inst = $rc->newInstance($request);
        } else {
            $inst = $rc->newInstance();
        }

        $ret = $method->invoke($inst, $matches);
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