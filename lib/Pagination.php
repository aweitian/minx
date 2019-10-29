<?php

/**
 * Date: 2015-1-7
 * Author: Awei.tian
 * function:
 */

namespace Aw;

class Pagination
{
    protected $totalLen;
    protected $curPageNum;
    protected $pageSize;
    protected $pageBtnLen;
    protected $rzRear;
    protected $data;
    /**
     * 函数调用不需要考虑从0开始,外部全部从1开始,返回的数据也是从1计数
     *
     * @param int $totalLen 总共数据记录长度
     * @param int $curPageNum 当前页
     * @param int $pageSize 一页多少条记录
     * @param int $pageBtnLen 显示多少个页按钮
     * @param bool $rzRear 按钮个数为偶数时,例如显示两个按钮,当前页为2,TRUE返回2,3,FALSE返回1,2
     */
    public function __construct($totalLen, $curPageNum, $pageSize, $pageBtnLen, $rzRear = true)
    {
        if (is_numeric($totalLen) && is_numeric($curPageNum) && is_numeric($pageSize) && is_numeric($pageBtnLen)) {
            $this->totalLen = $totalLen;
            $this->curPageNum = $curPageNum;
            $this->pageSize = $pageSize;
            $this->pageBtnLen = $pageBtnLen;
            if (is_bool($rzRear)) {
                $this->rzRear = $rzRear;
            } else {
                $this->rzRear = true;
            }
            if ($this->curPageNum < 1) {
                $this->curPageNum = 1;
            }
        } else {
            return;
        }

        $this->data = $this->getData();
    }

    public function getTotalRowNum()
    {
        return $this->totalLen;
    }

    /**
     *
     * @return number
     */
    public function getMaxPage()
    {
        return $this->data ["maxPage"];
    }

    /**
     *
     * @return number
     */
    public function getPageSize()
    {
        return $this->data ["pageSize"];
    }

    /**
     *
     * @return number
     */
    public function getCurPageNum()
    {
        return $this->data ["curPageNum"];
    }

    /**
     *
     * @return number
     */
    public function getPageBtnLen()
    {
        return $this->data ["pageBtnLen"];
    }

    /**
     *
     * @return number
     */
    public function getStartPage()
    {
        return $this->data ["start"];
    }

    /**
     *
     * @return number
     */
    public function getPre()
    {
        return $this->data ["pre"];
    }

    /**
     *
     * @return number
     */
    public function getNext()
    {
        return $this->data ["next"];
    }

    /**
     *
     * @return bool
     */
    public function hasPre()
    {
        return isset ($this->data ["pre"]);
    }

    /**
     *
     * @return bool
     */
    public function hasNext()
    {
        return isset ($this->data ["next"]);
    }

    /**
     * array(
     * "MaxPage"=>,
     * "pageSize"=>,
     * "curPageNum"=>,
     * "pageBtnLen"=>5,
     * "start"=> 1
     * "pre"=>*
     * "next"=>*
     * )
     * 这里面要注意两个概念,
     * 一个是PAGE
     * 一个是LENGTH
     */
    public function getData()
    {
        $ret = array();
        $ret ["maxPage"] = ceil($this->totalLen / $this->pageSize); // 类型:PAGE
        $ret ["pageSize"] = $this->pageSize; // 类型:LENGTH
        if ($this->curPageNum > $ret ["maxPage"]) {
            $this->curPageNum = $ret ["maxPage"]; // page 和 page比较
        }
        $ret ["curPageNum"] = $this->curPageNum; // 类型:PAGE
        if ($ret ["maxPage"] < $this->pageBtnLen) { // 当PAGE最大值小于等于LENGTH,可以互相转换
            $ret ["pageBtnLen"] = $ret ["maxPage"];
        } else {
            $ret ["pageBtnLen"] = $this->pageBtnLen;
        }

        $beginPos = $this->curPageNum - ( int )($ret ["pageBtnLen"] / 2); // 类型:PAGE
        $rearPos = $this->curPageNum + ( int )($ret ["pageBtnLen"] / 2); // 类型:PAGE
        // var_dump($ret["pageBtnLen"]);
        if ($beginPos < 1) {
            $beginPos = 1;
        }
        if ($rearPos > $ret ["maxPage"]) { // page 和 page比较
            $beginPos = $ret ["maxPage"] - $ret ["pageBtnLen"] + 1; // page - length 等于 page
        } else {
            // var_dump($ret["pageBtnLen"]);
            // 当按钮个数为偶数时
            if (0 == $ret ["pageBtnLen"] % 2) {
                // rzRear为当前显示按钮长度为4，当前页为4，是显示2，3，4，5还是3，4，5，6
                // 当前页面必须指向中间向右偏移一个位置的元素
                if ($this->rzRear && $this->curPageNum == $beginPos + $ret ["pageBtnLen"] / 2) {
                    $beginPos++;
                }
            }
        }

        $ret ["start"] = $beginPos;

        if ($this->curPageNum > 1) {
            $ret ["pre"] = $this->curPageNum - 1;
        }
        if ($this->curPageNum < $ret ["maxPage"]) {
            $ret ["next"] = $this->curPageNum + 1;
        }
        return $ret;
    }

    /**
     * 页从1计数,转换为内部计数从0开始
     * @param $e
     * @return int
     */
    private function e2i($e)
    {
        return $e - 1;
    }
//	/**
//	 * 内部计数从0开始,转换为页从1计数
//	 */
//	private function i2e($i) {
//		return $i + 1;
//	}
    public function getDbOffset()
    {
        return $this->e2i($this->curPageNum) * $this->pageSize;
    }

    public function getDbLength()
    {
        return $this->pageSize;
    }
}