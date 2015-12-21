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
        $esMock->shouldReceive('indexExists')->andReturn(true);
        $esMock->shouldReceive('createIndex');
        $esMock->shouldReceive('deleteIndex');

        $indexedModelMock = m::mock('alias:IndexedModelMock');
        $indexedModelMock->shouldReceive('putMapping')->once();
        $indexedModelMock->shouldReceive('addAllToIndex')->once();

        $esMock->shouldReceive('getIndexedModelClasses')->andReturn(['IndexedModelMock']);

        /** @var SearchBuildIndexCommand $cmd */
        $cmd = $this->app->make(SearchBuildIndexCommand::class, [$esMock]);

        $this->assertEquals(0, $cmd->handle());
    }

}