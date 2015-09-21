<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Spira\Auth\Driver\Guard;
use Spira\Contract\Exception\NotImplementedException;

class GuardTest extends TestCase
{
    public function testNotImplemented()
    {
        $guard = $this->getGuard();
        $this->setExpectedException(NotImplementedException::class, 'Not Implemented.');
        $guard->basic();

        $this->setExpectedException(NotImplementedException::class, 'Not Implemented.');
        $guard->onceBasic();

        $this->setExpectedException(NotImplementedException::class, 'Not Implemented.');
        $guard->validate();

        $this->setExpectedException(NotImplementedException::class, 'Not Implemented.');
        $guard->viaRemember();
    }

    /**
     * @return Guard
     */
    protected function getGuard()
    {
        return $this->app[Guard::class];
    }
}
