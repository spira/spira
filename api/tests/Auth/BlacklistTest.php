<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Mockery as m;
use Mockery\Mock;
use Spira\Auth\Blacklist\Blacklist;
use Spira\Auth\Blacklist\StorageInterface;
use Spira\Auth\Token\TokenExpiredException;

class BlacklistTest extends TestCase
{
    public function testAdd()
    {
        /** @var StorageInterface|Mock $driver */
        $driver = m::mock(StorageInterface::class);
        $blacklist = new Blacklist($driver, 'my_key', 'exp_key');

        $driver->shouldReceive('add')->with('my_key_value', \Mockery::anyOf(110, 109))->once()->andReturnNull();

        $this->assertNull($blacklist->add(['my_key' => 'my_key_value', 'exp_key' => time() + 100]));
    }

    public function testAddExpPast()
    {
        /** @var StorageInterface|Mock $driver */
        $driver = m::mock(StorageInterface::class);
        $blacklist = new Blacklist($driver, 'my_key', 'exp_key');

        $this->assertNull($blacklist->add(['my_key' => 'my_key_value', 'exp_key' => time() - 100]));
    }

    public function testCheckNoError()
    {
        /** @var StorageInterface|Mock $driver */
        $driver = m::mock(StorageInterface::class);
        $blacklist = new Blacklist($driver, 'my_key', 'exp_key');
        $driver->shouldReceive('get')->with('my_key_value')->once()->andReturnNull();

        $this->assertFalse($blacklist->check(['my_key' => 'my_key_value']));
    }

    public function testCheckExpired()
    {
        /** @var StorageInterface|Mock $driver */
        $driver = m::mock(StorageInterface::class);
        $blacklist = new Blacklist($driver, 'my_key', 'exp_key');
        $driver->shouldReceive('get')->with('my_key_value')->once()->andReturn(true);
        $this->setExpectedException(TokenExpiredException::class, 'Token has expired');
        $blacklist->check(['my_key' => 'my_key_value']);
    }

    public function testActualStorage()
    {
        $repo = m::mock('Illuminate\Contracts\Cache\Repository');
        $repo->shouldReceive('add')->with('some id', 'some id', 60)->once()->andReturn(true);
        $driver = new \Spira\Auth\Blacklist\CacheDriver($repo);
        $driver->add('some id');
    }
}
