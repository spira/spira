<?php


namespace App\Services;

use Illuminate\Validation\Validator;

class SpiraValidator extends Validator
{
    public function validateFloat($attribute, $value, $parameters)
    {
        return is_float($value + 0);
    }

    public function validateUuid($attribute, $value, $parameters)
    {
        return preg_match('/^\{?[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}\}?$/', $value);
    }
}
