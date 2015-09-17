<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 14.09.15
 * Time: 21:24.
 */

namespace Spira\Auth\Payload;

use Spira\Auth\Token\TokenInvalidException;

class PayloadValidationFactory
{
    /**
     * @var array
     */
    protected $validationRules;

    public function __construct(array $validationRules = [])
    {
        $this->validationRules = $validationRules;
    }

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
