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

    /**
     * Register custom validation rule for countries.
     *
     * @return void
     */
    protected function registerValidateCountry()
    {
        $this->validator->extend('country', function ($attribute, $value, $parameters) {
            $countries = $this->app->make('App\Services\Datasets\Countries')
                ->all()
                ->toArray();

            return in_array($value, array_fetch($countries, 'country_code'));
        });
    }
}
