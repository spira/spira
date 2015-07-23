<?php

namespace App\Services;

use Illuminate\Validation\Validator;
use Rhumsaa\Uuid\Uuid;

class SpiraValidator extends Validator
{
    public function validateFloat($attribute, $value, $parameters)
    {
        return is_float($value + 0);
    }

    public function validateUuid($attribute, $value, $parameters)
    {
        return Uuid::isValid($value);
    }
}
