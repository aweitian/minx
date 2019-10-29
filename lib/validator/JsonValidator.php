<?php

/**
 * @date 2017/7/10 17:19:45
 */

namespace Aw\Validator;

class JsonValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateItem($value)
    {
        $value = @json_decode($value);
        return !is_null($value);
    }
}

