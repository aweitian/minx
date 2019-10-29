<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/16
 * Time: 13:56
 */

namespace Aw\Filesystem;


class Condition
{
    const MODE_FILENAME = 0;
    const MODE_BASENAME = 1;

    const RETURN_FULL = 0;
    const RETURN_FILENAME = 1;
    const RETURN_BASENAME = 2;
    /**
     * @var string 说明ONLY,EXCEPT中是FILENAME还是BASENAME
     */
    protected $mode = 'filename';
    protected $only = array();
    protected $except = array();
    protected $return = 'full';

    public function __construct(array $data = array())
    {
        foreach ( $data as $key => $val ) {
            if (property_exists ( $this, $key )) {
                $this->{$key} = $val;
            }
        }
    }

    /**
     * @param array $data
     * @return Condition
     */
    public static function create(array $data = array())
    {
        return new self($data);
    }

    /**
     * @return $this
     */
    public function setModeFilename()
    {
        $this->mode = self::MODE_FILENAME;
        return $this;
    }

    /**
     * @return $this
     */
    public function setModeBasename()
    {
        $this->mode = self::MODE_BASENAME;
        return $this;
    }

    /**
     * @return bool
     */
    public function isModeFilename()
    {
        return $this->mode == self::MODE_FILENAME;
    }

    /**
     * @return bool
     */
    public function isModeBasename()
    {
        return $this->mode == self::MODE_BASENAME;
    }

    /**
     * @param array $only
     * @return $this
     */
    public function setOnly(array $only)
    {
        $this->only = $only;
        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isInOnly($key)
    {
        return in_array($key,$this->only);
    }

    /**
     * @param array $except
     * @return $this
     */
    public function setExcept(array $except)
    {
        $this->except = $except;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEmptyOnly()
    {
        return empty($this->only);
    }

    /**
     * @return bool
     */
    public function isEmptyExcept()
    {
        return empty($this->except);
    }

    /**
     * @param $key
     * @return array
     */
    public function isInExcept($key)
    {
        return in_array($key,$this->except);
    }

    /**
     * @return $this
     */
    public function setReturnFull()
    {
        $this->return = self::RETURN_FULL;
        return $this;
    }

    /**
     * @return $this
     */
    public function setReturnFilename()
    {
        $this->return = self::RETURN_FILENAME;
        return $this;
    }

    /**
     * @return $this
     */
    public function setReturnBasename()
    {
        $this->return = self::RETURN_BASENAME;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReturnFull()
    {
        return $this->return == self::RETURN_FULL;
    }

    /**
     * @return bool
     */
    public function isReturnFilename()
    {
        return $this->return == self::RETURN_FILENAME;
    }

    /**
     * @return bool
     */
    public function isReturnBasename()
    {
        return $this->return == self::RETURN_BASENAME;
    }
}