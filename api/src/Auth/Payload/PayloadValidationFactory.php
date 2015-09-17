<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Auth\Payload;

use Spira\Auth\Token\TokenInvalidException;

/**
 * Class PayloadValidationFactory
 * @package Spira\Auth\Payload
 */
class PayloadValidationFactory
{
    /**
     * @var array
     */
    protected $validationRules;

    /**
     * @param array $validationRules
     */
    public function __construct(array $validationRules = [])
    {
        $this->validationRules = $validationRules;
    }

    /**
     * @param $name
     * @param \Closure $function
     */
    public function addValidationRule($name, \Closure $function)
    {
        $this->validationRules[$name] = $function;
    }

    /**
     * @param $payload
     * @throw TokenInvalidException
     */
    public function validatePayload($payload)
    {
        foreach ($this->validationRules as $name => $rule) {
            if (isset($payload[$name]) && ! $rule($payload)) {
                throw new TokenInvalidException('Token invalid due to '.$name);
            }
        }
    }
}
