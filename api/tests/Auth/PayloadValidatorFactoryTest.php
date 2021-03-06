<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Spira\Auth\Payload\PayloadValidationFactory;
use Spira\Auth\Token\TokenInvalidException;

class PayloadValidatorFactoryTest extends TestCase
{
    public function testFactory()
    {
        $payload = ['one' => true, 'two' => 'two'];
        $closure = [
            'one' => function ($payload) {return $payload['one']; },
        ];

        $factory = new PayloadValidationFactory($closure);
        $this->assertNull($factory->validatePayload($payload));

        $factory->addValidationRule('two', function ($payload) {return $payload['two'] == 'two'; });
        $this->assertNull($factory->validatePayload($payload));
    }

    public function testRuleFailed()
    {
        $payload = ['one' => true, 'two' => 'two'];
        $closure = [
            'one' => function () {return false; },
        ];

        $factory = new PayloadValidationFactory($closure);
        $this->setExpectedException(TokenInvalidException::class, 'Token invalid due to one');
        $factory->validatePayload($payload);
    }

    public function testMissingRule()
    {
        $closure = [
            'three' => function ($payload) {return true; },
        ];

        $factory = new PayloadValidationFactory($closure);
        $this->assertNull($factory->validatePayload([]));
    }
}
