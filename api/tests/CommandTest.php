<?php

use Mockery as m;

class CommandTest extends TestCase
{
    public function testGenerateKeysCommand()
    {
        $file = m::mock('Illuminate\Filesystem\Filesystem');
        $file->shouldReceive('exists')->andReturn(true)
             ->shouldReceive('put')->andReturn(true);

        $this->app->instance('Illuminate\Filesystem\Filesystem', $file);
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
        $cmd = $this->app->make('App\Console\Commands\GenerateKeysCommand');

        $this->assertEquals(0, $cmd->handle());
    }
}
