<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

/**
 * Class ElasticSearchTest.
 */
class ElasticSearchTest extends TestCase
{
    public function testCreateIndex()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('create')->with(\Mockery::subset(['index' => config()->get('elasticquent.default_index')]));

        $elasticSearchService = new \App\Services\ElasticSearch($elasticSearchMock);

        $elasticSearchService->createIndex();
    }

    public function testCreateIndexForModel()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('create')->with(\Mockery::subset(['index' => 'foo']));

        $elasticSearchService = new \App\Services\ElasticSearch($elasticSearchMock);

        $elasticSearchService->createIndex(new class extends \Spira\Core\Model\Model\IndexedModel {

            public function getIndexName()
            {
                return 'foo';
            }

        });
    }

    public function testDeleteIndex()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('delete')->with(\Mockery::subset(['index' => config()->get('elasticquent.default_index')]));

        $elasticSearchService = new \App\Services\ElasticSearch($elasticSearchMock);

        $elasticSearchService->deleteIndex();
    }

    public function testDeleteIndexForModel()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('delete')->with(\Mockery::subset(['index' => 'foo']));

        $elasticSearchService = new \App\Services\ElasticSearch($elasticSearchMock);

        $elasticSearchService->deleteIndex(new class extends \Spira\Core\Model\Model\IndexedModel {

            public function getIndexName()
            {
                return 'foo';
            }

        });
    }

    public function testIndexExists()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('exists')->with(\Mockery::subset(['index' => 'foo']));

        $elasticSearchService = new \App\Services\ElasticSearch($elasticSearchMock);

        $elasticSearchService->indexExists(new class extends \Spira\Core\Model\Model\IndexedModel {

            public function getIndexName()
            {
                return 'foo';
            }

        });
    }

    public function testIndexExistsForModel()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class)->makePartial();
        $indicesMock = Mockery::mock(\Elasticsearch\Namespaces\IndicesNamespace::class);

        $elasticSearchMock->shouldReceive('indices')->andReturn($indicesMock);
        $indicesMock->shouldReceive('exists')->with(\Mockery::subset(['index' => 'foo']));

        $elasticSearchService = new \App\Services\ElasticSearch($elasticSearchMock);

        $elasticSearchService->indexExists(new class extends \Spira\Core\Model\Model\IndexedModel {

            public function getIndexName()
            {
                return 'foo';
            }

        });
    }

    public function testGetIndexedModels()
    {
        $elasticSearchMock = Mockery::mock(\Elasticsearch\Client::class);
        $elasticSearchService = new \App\Services\ElasticSearch($elasticSearchMock);

        $classes = $elasticSearchService->getIndexedModelClasses();

        $this->assertInternalType('array', $classes);

        foreach($classes as $className){
            $this->assertInstanceOf(\Spira\Core\Model\Model\IndexedModel::class, new $className);
        }

    }

}
