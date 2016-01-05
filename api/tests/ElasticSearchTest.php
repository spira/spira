<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
use App\Services\ElasticSearch;
use Spira\Core\Model\Model\IndexedModel;

/**
 * Class ElasticSearchTest.
 */
class ElasticSearchTest extends TestCase
{
    private $indexedModelMock;

    public function setUp()
    {
        parent::setUp();
        $indexedModelMock = Mockery::namedMock('NamedIndexedModelMock', IndexedModel::class);
        $indexedModelMock->shouldReceive('getIndexName')->andReturn('foo');

        $this->indexedModelMock = $indexedModelMock;
    }

    public function testCreateIndex()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('create')->with(\Mockery::subset(['index' => config()->get('elasticquent.default_index')]));

        $elasticSearchService = new ElasticSearch($elasticSearchMock, config()->get('elasticquent.default_index'));

        $elasticSearchService->createIndex();
    }

    public function testCreateIndexForModel()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('create')->with(\Mockery::subset(['index' => 'foo']));

        $elasticSearchService = new ElasticSearch($elasticSearchMock, config()->get('elasticquent.default_index'));

        $elasticSearchService->createIndex($this->indexedModelMock);
    }

    public function testDeleteIndex()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('delete')->with(\Mockery::subset(['index' => config()->get('elasticquent.default_index')]));

        $elasticSearchService = new ElasticSearch($elasticSearchMock, config()->get('elasticquent.default_index'));

        $elasticSearchService->deleteIndex();
    }

    public function testDeleteIndexForModel()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('delete')->with(\Mockery::subset(['index' => 'foo']));

        $elasticSearchService = new ElasticSearch($elasticSearchMock, config()->get('elasticquent.default_index'));

        $elasticSearchService->deleteIndex($this->indexedModelMock);
    }

    public function testIndexExists()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('exists')->with(\Mockery::subset(['index' => 'foo']));

        $elasticSearchService = new ElasticSearch($elasticSearchMock, config()->get('elasticquent.default_index'));

        $elasticSearchService->indexExists($this->indexedModelMock);
    }

    public function testIndexExistsForModel()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('exists')->with(\Mockery::subset(['index' => 'foo']));

        $elasticSearchService = new ElasticSearch($elasticSearchMock, config()->get('elasticquent.default_index'));

        $elasticSearchService->indexExists($this->indexedModelMock);
    }

    public function testGetIndexedModels()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class);
        $elasticSearchService = new ElasticSearch($elasticSearchMock, config()->get('elasticquent.default_index'));

        $classes = $elasticSearchService->getIndexedModelClasses();

        $this->assertInternalType('array', $classes);

        foreach ($classes as $className) {
            $this->assertInstanceOf(IndexedModel::class, new $className);
        }
    }

    public function testRebuildIndex()
    {
        $elasticSearchService = Mockery::mock(ElasticSearch::class)->makePartial();

        $indexedModelMock = Mockery::mock('alias:IndexedModelMock');
        $indexedModelMock->shouldReceive('putMapping')->once();
        $indexedModelMock->shouldReceive('deleteCustomIndexes')->once();
        $indexedModelMock->shouldReceive('createCustomIndexes')->once();

        $elasticSearchService->shouldReceive('indexExists')->once()->andReturn(true);
        $elasticSearchService->shouldReceive('deleteIndex')->once();
        $elasticSearchService->shouldReceive('createIndex')->once();
        $elasticSearchService->shouldReceive('getIndexedModelClasses')->andReturn(['IndexedModelMock']);

        $elasticSearchService->reindexAll(false);
    }

    public function testReindexAll()
    {
        $elasticSearchService = Mockery::mock(ElasticSearch::class)->makePartial();

        $indexedModelMock = Mockery::mock('alias:IndexedModelMock');
        $indexedModelMock->shouldReceive('putMapping')->once();
        $indexedModelMock->shouldReceive('addAllToIndex')->once();
        $indexedModelMock->shouldReceive('deleteCustomIndexes')->once();
        $indexedModelMock->shouldReceive('createCustomIndexes')->once();

        $elasticSearchService->shouldReceive('indexExists')->once()->andReturn(true);
        $elasticSearchService->shouldReceive('deleteIndex')->once();
        $elasticSearchService->shouldReceive('createIndex')->once();
        $elasticSearchService->shouldReceive('getIndexedModelClasses')->andReturn(['IndexedModelMock']);

        $elasticSearchService->reindexAll(true);
    }
}
