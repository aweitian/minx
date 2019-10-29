<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/10
 * Time: 16:47
 */

namespace Aw\Routing\Matcher;


abstract class Matcher implements IMatcher
{
    public $result = array();
    protected $contain_url_matcher = false;

    public function getMatchResult()
    {
        return $this->result;
    }

    public function hasUrlMatcher()
    {
        return $this->contain_url_matcher;
    }
}