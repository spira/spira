<?php namespace App\Services;

use Rhumsaa\Uuid\Uuid;
use Spira\Repository\Validation\Validator;

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

    public function validateNotFound()
    {
        return false;
    }

    public function validateEquals($attribute, $value, $parameters)
    {
        $this->requireParameterCount(1,$parameters, 'equals');
        $compare = $parameters[0];

        if ($compare == $value) {
            return true;
        }

        return false;
    }

    /**
     * Register custom validation rule for countries.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @param  array   $parameters
     * @return void
     */
    protected function validateCountry($attribute, $value, $parameters)
    {
        $countries = \App::make('App\Services\Datasets\Countries')
            ->all()
            ->toArray();

        return in_array($value, array_fetch($countries, 'country_code'));
    }
}
