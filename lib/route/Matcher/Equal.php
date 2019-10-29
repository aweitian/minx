<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/25
 * Time: 12:43
 */

namespace Aw\Routing\Matcher;


use Aw\Http\Request;

class Equal extends Matcher
{
    protected $url;

    /**
     * /ab/c  == /ab/c/
     * Equal constructor.
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function match(Request $request)
    {
        $url = $request->getPath();
        return rtrim($url, '/') === rtrim($this->url, '/');
    }

    public function hasUrlMatcher()
    {
        return true;
    }
}