<?php
/**
 * @date 2017/7/10 13:38:45
 */

namespace Aw\Validator;

class CompareValidator extends Validator
{
    const OPERATOR_EQU = '=';
    const OPERATOR_NOT_EQU = '!=';
    const OPERATOR_GT = '>';
    const OPERATOR_GE = '>=';
    const OPERATOR_LT = '<';
    const OPERATOR_LE = '<=';
    /**
     *
     * @var string the constant value to be compared with
     */
    public $compareValue;
    /**
     *
     * @var string the operator for comparison. Defaults to '='.
     *      The followings are valid operators:
     *      <ul>
     *      <li>'=' or '==': validates to see if the two values are equal. If {@link strict} is true, the comparison
     *      will be done in strict mode (i.e. checking value type as well).</li>
     *      <li>'!=': validates to see if the two values are NOT equal. If {@link strict} is true, the comparison
     *      will be done in strict mode (i.e. checking value type as well).</li>
     *      <li>'>': validates to see if the value being validated is greater than the value being compared with.</li>
     *      <li>'>=': validates to see if the value being validated is greater than or equal to the value being compared with.</li>
     *      <li>'<': validates to see if the value being validated is less than the value being compared with.</li>
     *      <li>'<=': validates to see if the value being validated is less than or equal to the value being compared with.</li>
     *      </ul>
     */
    public $operator = self::OPERATOR_EQU;

    /**
     * @inheritdoc
     */
    public function validateItem($value)
    {
        if ($this->allowEmpty && $this->isEmpty($value))
            return true;
        $compareValue = $this->compareValue;

        switch ($this->operator) {
            case '=' :
            case '==' :
                if (($this->strict && $value !== $compareValue) || (!$this->strict && $value != $compareValue)) {
                    $this->message = '{attribute} must be repeated exactly {compareValue}.';
                    $this->message = strtr($this->message, array(
                        '{compareValue}' => $compareValue
                    ));
                    return false;
                }
                return true;
            case '!=' :
                if (($this->strict && $value === $compareValue) || (!$this->strict && $value == $compareValue)) {
                    $this->message = '{attribute} must not be equal to "{compareValue}"';
                    $this->message = strtr($this->message, array(
                        '{compareValue}' => $compareValue
                    ));
                    return false;
                }
                return true;
            case '>' :
                if ($value <= $compareValue) {
                    $this->message = '{attribute} must be greater than "{compareValue}".';
                    $this->message = strtr($this->message, array(
                        '{compareValue}' => $compareValue
                    ));
                    return false;
                }
                return true;
            case '>=' :
                if ($value < $compareValue) {
                    $this->message = '{attribute} must be greater than or equal to "{compareValue}".';
                    $this->message = strtr($this->message, array(
                        '{compareValue}' => $compareValue
                    ));
                    return false;
                }
                return true;;
            case '<' :
                if ($value >= $compareValue) {
                    $this->message = '{attribute} must be less than "{compareValue}".';
                    $this->message = strtr($this->message, array(
                        '{compareValue}' => $compareValue
                    ));
                    return false;
                }
                return true;
            case '<=' :
                if ($value > $compareValue) {
                    $this->message = '{attribute} must be less than or equal to "{compareValue}".';
                    $this->message = strtr($this->message, array(
                        '{compareValue}' => $compareValue
                    ));
                    return false;
                }
                return true;
            default :
                $this->message = strtr('Invalid operator "{operator}".', array(
                    '{operator}' => $this->operator
                ));
                return false;
        }
    }
}
