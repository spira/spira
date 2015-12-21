<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Mockery as m;
use App\Console\Commands\GenerateKeysCommand;

/**
 * Class GenerateKeysCommandTest
 * @group commands
 */
class GenerateKeysCommandTest extends TestCase
{
    public function testGenerateKeysCommand()
    {
        $file = m::mock('Illuminate\Filesystem\Filesystem');
        $file->shouldReceive('exists')->andReturn(true)
            ->shouldReceive('put')->andReturn(true);

        $this->app->instance('Illuminate\Filesystem\Filesystem', $file);
        /** @var GenerateKeysCommand $cmd */
        $cmd = $this->app->make('App\Console\Commands\GenerateKeysCommand');

        $this->assertEquals(0, $cmd->handle());
    }

    public function testGenerateKeysCommandMakeDirectory()
    {
        $file = m::mock('Illuminate\Filesystem\Filesystem');
        $file->shouldReceive('exists')->andReturn(false)
            ->shouldReceive('makeDirectory')->andReturn(true)
            ->shouldReceive('put')->andReturn(true);

        $this->app->instance('Illuminate\Filesystem\Filesystem', $file);
        /** @var GenerateKeysCommand $cmd */
        $cmd = $this->app->make('App\Console\Commands\GenerateKeysCommand');

        $this->assertEquals(0, $cmd->handle());
    }

}
