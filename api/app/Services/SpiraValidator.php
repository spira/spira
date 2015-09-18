<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services;

use Rhumsaa\Uuid\Uuid;
use Spira\Model\Validation\Validator;

class SpiraValidator extends Validator
{
    public function validateDecimal($attribute, $value, $parameters)
    {
        return is_float($value) || is_int($value);
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
        $this->requireParameterCount(1, $parameters, 'equals');
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
     * @return bool
     */
    protected function validateCountry($attribute, $value, $parameters)
    {
        $countries = \App::make('App\Services\Datasets\Countries')
            ->all()
            ->toArray();

        return in_array($value, array_fetch($countries, 'country_code'));
    }

    /**
     * Register custom validation rule for alpha numeric dash with spaces.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @param  array   $parameters
     * @return void
     */
    protected function validateAlphaDashSpace($attribute, $value, $parameters)
    {
        return preg_match('/^[\pL\pN\s._-]+$/u', $value);
    }

    /**
     * Register custom validation rule for supported region codes.
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    protected function validateSupportedRegion($attribute, $value, $parameters)
    {
        $supportedRegionCodes = array_pluck(config('regions.supported'), 'code');

        return in_array($value, $supportedRegionCodes);
    }
}
