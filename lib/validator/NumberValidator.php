<?php

/**
 * @date 2017/7/10 13:38:45
 */

namespace Aw\Validator;

class NumberValidator extends Validator
{
    /**
     *
     * @var boolean whether the attribute value can only be an integer. Defaults to false.
     */
    public $integerOnly = false;


    /**
     *
     * @var boolean whether the attribute value can only be an integer. Defaults to false.
     */
    public $unsignedOnly = false;
    /**
     *
     * @var boolean whether the attribute value can be null or empty. Defaults to true,
     *      meaning that if the attribute is empty, it is considered valid.
     */
    public $allowEmpty = true;
    /**
     *
     * @var integer|double upper limit of the number. Defaults to null, meaning no upper limit.
     */
    public $max;
    /**
     *
     * @var integer|double lower limit of the number. Defaults to null, meaning no lower limit.
     */
    public $min;

    /**
     * @inheritdoc
     */
    public function validateItem($value)
    {
        if ($this->unsignedOnly && strpos($value, "-") === 0)
            return false;

        if ($this->allowEmpty && $this->isEmpty($value))
            return true;
        if ($this->integerOnly) {
            if (!preg_match('/^\s*[+-]?\d+\s*$/', "$value")) {
                $this->message = '{attribute} must be an integer.';
                return false;
            }
        } else {
            if (!preg_match('/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/', "$value")) {
                $this->message = '{attribute} must be a number.';
                return false;
            }
        }
        if ($this->min !== null && $value < $this->min) {
            $this->message = strtr('{attribute} is too small (minimum is {min}).', array(
                '{min}' => $this->min
            ));
            return false;
        }
        if ($this->max !== null && $value > $this->max) {
            $this->message = strtr('{attribute} is too big (maximum is {max}).', array(
                '{max}' => $this->max
            ));
            return false;
        }
        return true;
    }
}
