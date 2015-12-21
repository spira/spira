<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Mockery as m;
use App\Console\Commands\SearchBuildIndexCommand;

/**
 * Class SearchBuildIndexCommandTest
 * @group commands
 * @group testing
 */
class SearchBuildIndexCommandTest extends TestCase
{
    public function testSearchBuildIndexCommand()
    {

        $esMock = m::mock(\Elasticsearch\Client::class);
        $esMock->shouldReceive('exists')->andReturn(true)
            ->shouldReceive('put')->andReturn(true);

        $this->app->instance(\Elasticsearch\Client::class, $esMock);

        /** @var SearchBuildIndexCommand $cmd */
        $cmd = $this->app->make(SearchBuildIndexCommand::class);

        $this->assertEquals(0, $cmd->handle());
    }

}
