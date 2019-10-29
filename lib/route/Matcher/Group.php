<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/25
 * Time: 12:43
 */

namespace Aw\Routing\Matcher;


use Aw\Http\Request;

class Group extends Matcher
{
    protected $url;

    protected $array = array();

    /**
     * @var IMatcher
     */
    protected $url_matcher = null;

    public function add(IMatcher $matcher)
    {
        $this->array[] = $matcher;
    }
//
//    public function addUrlMatcher(IMatcher $matcher)
//    {
//        $this->hasUrlMatcher = true;
//        $this->url_matcher = $matcher;
//        $this->add($matcher);
//    }
//
//    public function getUrlMatcher()
//    {
//        return $this->url_matcher;
//    }

    /**
     *
     * @param Request $request
     * @return bool
     */
    public function match(Request $request)
    {
        /**
         * @var IMatcher $matcher
         */
        if (empty($this->array))
            return false;
        foreach ($this->array as $matcher) {
            if ($matcher->hasUrlMatcher()) {
                $this->url_matcher = $matcher;
            }
            if (!$matcher->match($request))
                return false;
        }
        //url matcher 向上传递
        //url必须匹配成功才能执行到这
        if (!is_null($this->url_matcher)) {
            $this->contain_url_matcher = true;
            $this->result = $this->url_matcher->getMatchResult();
        }
        return true;
    }


}