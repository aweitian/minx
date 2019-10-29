<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/25
 * Time: 12:43
 */

namespace Aw\Routing\Matcher;


use Aw\Http\Request;

class Regexp extends Matcher
{
    const DELIMITER = '#';
    protected $regexp;
    /**
     * mca MASK
     * @var string
     */
    protected $mca_mask = '01';
    public $result = array();

    public function __construct($regexp)
    {
        $this->regexp = $regexp;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function match(Request $request)
    {
        if (!$this->regexp) {
            return false;
        }
        $url = $request->getPath();
        if (substr($this->regexp, 0, 1) !== self::DELIMITER || substr($this->regexp, -1, 1) !== self::DELIMITER) {
            $this->regexp = self::DELIMITER . $this->regexp . self::DELIMITER;
        }
        return !!preg_match($this->regexp, $url, $this->result);
    }

    /**
     * 可以是两个数字,也可以是三个数字
     * 二个数字使用CA模式,三个数字使用MCA
     * @param $mask
     * @return bool
     * @throws \Exception
     */
    public function setMask($mask)
    {
        if (preg_match('/^\d\d\d?$/', $mask)) {
            $this->mca_mask = $mask;
            return true;
        } else {
            throw new \Exception("mask must be match \d{2,3}");
        }
    }

    /**
     * @return string
     */
    public function getMask()
    {
        return $this->mca_mask;
    }

    public function hasUrlMatcher()
    {
        return true;
    }
}