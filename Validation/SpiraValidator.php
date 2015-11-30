<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\Validation;

use Illuminate\Auth\Access\Gate;
use Illuminate\Support\Arr;
use Rhumsaa\Uuid\Uuid;

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

    public function validateRbacRoleExists($attribute, $value, $parameters)
    {
        return (bool) $this->getGate()->getStorage()->getItem($value);
    }

    public function validateNotRequiredIf($attribute, $value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'not_required_if');

        $data = Arr::get($this->data, $parameters[0]);

        $values = array_slice($parameters, 1);

        if (in_array((string) $data, $values)) {
            return false;
        }

        return true;
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

    /**
     * @return Gate
     */
    public function getGate()
    {
        return app(Gate::class);
    }

    /**
     * Validate whether the attribute is decoded json (object or array).
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    protected function validateDecodedJson($attribute, $value, $parameters)
    {
        return is_object($value) || is_array($value);
    }
}
