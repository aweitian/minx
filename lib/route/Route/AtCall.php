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
use Aw\Routing\Dispatch\AtCall as AtCallDispatcher;
use Aw\Routing\Matcher\IMatcher;
use Exception;

class AtCall extends Route
{
    public static $defaultClass = "main";
    public static $defaultMethod = "index";
    protected $at_call_ns;
    protected $at_call;

    public function __construct(IMatcher $matcher, $at_call, $at_call_ns = null)
    {
        $this->matcher = $matcher;
        $this->at_call_ns = $at_call_ns;
        $this->at_call = $at_call;
        $this->newHook();
    }

    /**
     * (:1|def)
     * 处理action上的占位符,默认值
     */
    protected function preFilterCallback()
    {
        if (is_string($this->at_call_ns)) {
            $this->dispatcher = new AtCallDispatcher($this->at_call, $this->at_call_ns);
        } else {
            $this->dispatcher = new AtCallDispatcher($this->at_call);
        }

        $trans = array();
        $result = $this->matcher->getMatchResult();
        $c = preg_split("/[\\\@]/", $this->dispatcher->callback);
        foreach ($c as $item) {
            if (preg_match('#\(\:(\d+)(\|\w+)?\)#', $item, $m)) {
                //$trans[$item] = $m;
                if (!isset($result[$m[1]])) {
                    //下标索引不存在,必须得有默认值
                    if (isset($m[2])) {
                        $trans[$item] = trim($m[2], "|");
                    } else {
                        if ($m[1] == 1) {
                            $trans[$item] = AtCall::$defaultClass;
                        } elseif ($m[1] == 2) {
                            $trans[$item] = AtCall::$defaultMethod;
                        } else {
                            throw new Exception("$item 下标索引不存在,并且没有默认值");
                        }
                    }
                } else {
                    $trans[$item] = $result[$m[1]];
                }
            }
        }
//        var_dump($trans, $this->dispatcher->callback);
        if (!empty($trans))
            $this->dispatcher->callback = strtr($this->dispatcher->callback, $trans);
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

        $this->preFilterCallback();
        $this->beforeDispatcher($request, $this->matcher, $this->dispatcher);
        $that = $this;
        $pipe = new Pipeline();
        return $pipe->send($request)
            ->through($middleware)
            ->then(function ($request) use ($that) {
                $f = $that->dispatcher->dispatch($request, $that->matcher->getMatchResult());
                $that->result = $that->dispatcher->getResponse();
                return $f;
            });
    }
}