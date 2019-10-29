<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/25
 * Time: 12:43
 */

namespace Aw\Routing\Matcher;


use Aw\Http\Request;

class OrGroup extends Matcher
{
    protected $url;

    protected $array = array();

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
            if ($matcher->match($request)) {
                //url matcher 向上传递
                if ($matcher->hasUrlMatcher()) {
                    $this->contain_url_matcher = true;
                    $this->result = $matcher->getMatchResult();
                }
                return true;
            }
        }
        return false;
    }
}