<?php

/**
 * @date 2017/7/10 17:19:45
 */

namespace Aw\Validator;

class RegularExpressionValidator extends Validator
{
    /**
     *
     * @var string the regular expression to be matched with
     */
    public $pattern;

    /**
     * @inheritdoc
     */
    public function validateItem($value)
    {
        if ($this->allowEmpty && $this->isEmpty($value))
            return true;
        if ($this->pattern === null) {
            $this->message = 'The "pattern" property must be specified with a valid regular expression.';
            return false;
        }
        if (!preg_match($this->pattern, $value)) {
            $this->message = '{attribute} is invalid.';
            return false;
        }
        return true;
    }
}

