<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/25
 * Time: 12:43
 */

namespace Aw\Routing\Matcher;


use Aw\Http\Request;

class Method extends Matcher
{
    protected $method = 'GET';

    public function __construct($method = 'GET')
    {
        $this->method = strtoupper($method);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function match(Request $request)
    {
        return $request->getMethod() === $this->method;
    }

    public function hasUrlMatcher()
    {
        return false;
    }
}