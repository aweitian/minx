<?php

/**
 * @date 2017/7/10 13:38:45
 */

namespace Aw\Validator;

class EmailValidator extends Validator
{
    /**
     *
     * @var string the regular expression used to validate the attribute value.
     * @see http://www.regular-expressions.info/email.html
     */
    public $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
    /**
     *
     * @var string the regular expression used to validate email addresses with the name part.
     *      This property is used only when {@link allowName} is true.
     * @since 1.0.5
     * @see allowName
     */
    public $fullPattern = '/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';
    /**
     *
     * @var boolean whether to allow name in the email address (e.g. "Qiang Xue <qiang.xue@gmail.com>"). Defaults to false.
     * @since 1.0.5
     * @see fullPattern
     */
    public $allowName = false;
    /**
     *
     * @var boolean whether to check the MX record for the email address.
     *      Defaults to false. To enable it, you need to make sure the PHP function 'checkdnsrr'
     *      exists in your PHP installation.
     */
    public $checkMX = false;
    /**
     *
     * @var boolean whether to check port 25 for the email address.
     *      Defaults to false.
     * @since 1.0.4
     */
    public $checkPort = false;

    /**
     * @inheritdoc
     */
    public function validateItem($value)
    {
        if ($this->allowEmpty && $this->isEmpty($value))
            return true;
        if (!$this->validateValue($value)) {
            $this->message = '{attribute} is not a valid email address.';
            return false;
        }
        return true;
    }

    /**
     * Validates a static value to see if it is a valid email.
     * Note that this method does not respect {@link allowEmpty} property.
     * This method is provided so that you can call it directly without going through the model validation rule mechanism.
     *
     * @param
     *            mixed the value to be validated
     * @return boolean whether the value is a valid email
     * @since 1.1.1
     */
    private function validateValue($value)
    {
        $valid = is_string($value) && (preg_match($this->pattern, $value) || $this->allowName && preg_match($this->fullPattern, $value));
        if ($valid)
        {
            $domain = rtrim(substr($value, strpos($value, '@') + 1), '>');
            if ($valid && $this->checkMX && function_exists('checkdnsrr'))
                $valid = checkdnsrr($domain, 'MX');
            if ($valid && $this->checkPort && function_exists('fsockopen'))
                $valid = fsockopen($domain, 25) !== false;
        }
        return $valid;
    }
}
