<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/25
 * Time: 12:43
 */

namespace Aw\Routing\Matcher;


use Aw\Http\Request;
use Closure;

class Callback extends Matcher
{
    protected $callback;

    /**
     * Callback constructor.
     * @param callable|Closure $callback
     */
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function match(Request $request)
    {
        $callback = $this->callback;
        if (!is_callable($callback)) return false;
        return !!$callback($request, $this);
    }

    public function hasUrlMatcher()
    {
        return true;
    }
}