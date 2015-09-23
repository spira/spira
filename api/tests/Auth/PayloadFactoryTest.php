<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Contracts\Auth\Authenticatable;
use Mockery as m;
use Spira\Auth\Payload\PayloadFactory;

class PayloadFactoryTest extends TestCase
{
    public function testFactory()
    {
        $closures = [
            'first' => function () { return 'first'; },
            'second' => function () { return 'second'; },
        ];
        $array = ['first' => 'first','second' => 'second'];

        $factory = new PayloadFactory($closures);
        $user = m::mock(Authenticatable::class);
        $this->assertEquals($array, $factory->createFromUser($user));

        $factory->addPayloadGenerator('user', function ($user) {return $user;});

        $array['user'] = $user;

        $this->assertEquals($array, $factory->createFromUser($user));
    }
}
