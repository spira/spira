<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Auth\Payload;

use Illuminate\Contracts\Auth\Authenticatable;

class PayloadFactory
{
    /**
     * @var array
     */
    protected $payloadGenerators;

    public function __construct(array $payloadGenerators = [])
    {
        $this->payloadGenerators = $payloadGenerators;
    }

    public function createFromUser(Authenticatable $user)
    {
        $payload = [];
        foreach ($this->payloadGenerators as $name => $generator) {
            if ($generator instanceof \Closure) {
                $result = $generator($user);
                if ($result !== false) {
                    $payload[$name] = $result;
                }
            }
        }

        return $payload;
    }

    public function addPayloadGenerator($name, \Closure $function)
    {
        $this->payloadGenerators[$name] = $function;
    }
}
