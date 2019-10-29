<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 8:54
 *
 */

namespace Aw;


use DateTime;

class Carbon extends DateTime
{
    /**
     * @return Carbon
     */
    public static function now()
    {
        return new self('now');
    }

    /**
     * @return int
     */
    public function time()
    {
        return $this->getTimestamp();
    }

    /**
     * @param int $value
     * @return $this
     */
    public function addMonths($value = 1)
    {
        $modify = ($value > 0 ? "+$value" : "-" . abs($value)) . " month";
        $this->modify($modify);
        return $this;
    }

    /**
     * @return Carbon
     */
    public function addMonth()
    {
        return $this->addMonths();
    }

    /**
     * @param int $value
     * @return $this
     */
    public function addYears($value = 1)
    {
        $modify = ($value > 0 ? "+$value" : "-" . abs($value)) . " year";
        $this->modify($modify);
        return $this;
    }

    /**
     * @return Carbon
     */
    public function addYear()
    {
        return $this->addYears();
    }

    /**
     * @return Carbon
     */
    public function subYear()
    {
        return $this->addYears(-1);
    }

    /**
     * @return Carbon
     */
    public function subMonth()
    {
        return $this->addMonths(-1);
    }

    /**
     * @param int $value
     * @return Carbon
     */
    public function subMonths($value = 1)
    {
        return $this->addMonths(-1 * $value);
    }

    /**
     * @param int $value
     * @return Carbon
     */
    public function addDays($value = 1)
    {
        $modify = ($value > 0 ? "+$value" : "-" . abs($value)) . " day";
        $this->modify($modify);
        return $this;
    }

    /**
     * @return Carbon
     */
    public function addDay()
    {
        return $this->addDays();
    }

    /**
     * @return Carbon
     */
    public function subDay()
    {
        return $this->addDays(-1);
    }

    /**
     * @param int $value
     * @return Carbon
     */
    public function subDays($value = 1)
    {
        return $this->addDays(-1 * $value);
    }

    /**
     * @return Carbon
     */
    public function addWeek()
    {
        return $this->addDays(7);
    }

    /**
     * @return Carbon
     */
    public function subWeek()
    {
        return $this->addDays(-7);
    }

    /**
     * 切换到月底
     * @return $this
     */
    public function lastDayOfMonth()
    {
        $this->modify('last day of this month');
        $this->setTime(23, 59, 59);
        return $this;
    }

    /**
     * 切换到月初
     * @return $this
     */
    public function firstDayOfMonth()
    {
        $this->modify('first day of this month');
        $this->setTime(0, 0, 0);
        return $this;
    }

    /**
     * 切换到星期天
     * @return $this
     */
    public function lastDayOfWeek()
    {
        $this->addDays(7 - $this->format('w'));
        $this->setTime(23, 59, 59);
        return $this;
    }

    /**
     * 切换到星期一
     * @return $this
     */
    public function firstDayOfWeek()
    {
        $this->addDays(1 - $this->format('w'));
        $this->setTime(0, 0, 0);
        return $this;
    }
}