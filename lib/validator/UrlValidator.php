<?php

/**
 * @date 2017/7/10 17:19:45
 */

namespace Aw\Validator;

class UrlValidator extends Validator
{
    /**
     *
     * @var string the regular expression used to validates the attribute value.
     */
    public $pattern = '/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)$/i';
    public $domain = '/^(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)$/i';
    public $strict = true;
    public $isDomain = false;

    /**
     * @inheritdoc
     */
    public function validateItem($value)
    {
        if ($this->allowEmpty && $this->isEmpty($value))
            return true;
        if (!$this->validateValue($value)) {
            $this->message = '{attribute} is not a valid URL.';
            return false;
        }
        return true;
    }

    protected function validateValue($value)
    {
        if (!is_string($value))
            return false;
        if ($this->strict) {
            if ($this->isDomain) {
                return !!preg_match($this->domain, $value);
            } else {
                return !!preg_match($this->pattern, $value);
            }
        } else {
            if (preg_match($this->domain, $value)) {
                return true;
            }
            return !!preg_match($this->pattern, $value);
        }
    }
}

