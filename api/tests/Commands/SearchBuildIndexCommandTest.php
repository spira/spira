<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Mockery as m;
use App\Services\ElasticSearch;
use App\Console\Commands\SearchBuildIndexCommand;

/**
 * Class SearchBuildIndexCommandTest
 * @group commands
 */
class SearchBuildIndexCommandTest extends TestCase
{
    public function testSearchBuildIndexCommand()
    {
        $esMock = m::mock(ElasticSearch::class);
        $esMock->shouldReceive('reindexAll')->once()->with(false)->andReturn(true);

        /** @var SearchBuildIndexCommand $cmdMocked */
        $cmdMocked = \Mockery::mock(SearchBuildIndexCommand::class.'[option]', [$esMock]);
        $cmdMocked->shouldReceive('option')->with('addtoindex')->andReturn(false);

        $this->assertEquals(0, $cmdMocked->handle());
    }

    public function testSearchBuildIndexCommandReindex()
    {
        $esMock = m::mock(ElasticSearch::class);
        $esMock->shouldReceive('reindexAll')->once()->with(true)->andReturn(true);

        /** @var SearchBuildIndexCommand $cmdMocked */
        $cmdMocked = \Mockery::mock(SearchBuildIndexCommand::class.'[option]', [$esMock]);
        $cmdMocked->shouldReceive('option')->with('addtoindex')->andReturn(true);

        $this->assertEquals(0, $cmdMocked->handle());
    }

}