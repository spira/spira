<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Rhumsaa\Uuid\Uuid;
use App\Models\TestEntity;
use App\Models\Localization;
use App\Models\SecondTestEntity;

/**
 * Class EntityTest.
 * @group integration
 */
class EntityTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
        TestEntity::flushEventListeners();
        TestEntity::boot();
    }

    /**
     * @param TestEntity $model
     */
    protected function addRelatedEntities($model)
    {
        $this->getFactory(SecondTestEntity::class)->count(5)->make()->each(function ($secondEntity) use ($model) {
            $model->testMany()->save($secondEntity);
        });
    }

    public function testGetAll()
    {
        $this->getJson('/test/entities');

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
    }

    public function testGetAllWithNested()
    {
        $entity = $this->getFactory(TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $this->getJson('/test/entities', ['with-nested' => 'testMany']);
        $objects = $this->getJsonResponse();

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();

        $asserted = false;
        foreach ($objects as $object) {
            if (count($object->_testMany) == 5) {
                $asserted = true;
                $this->assertEquals(5, count($object->_testMany));
                foreach ($object->_testMany as $nestedObject) {
                    $this->assertObjectHasAttribute('_self', $nestedObject);
                    $this->assertTrue(is_string($nestedObject->_self), '_self is a string');
                    $this->assertObjectHasAttribute('entityId', $nestedObject);
                    $this->assertUuid($nestedObject->entityId);
                }
            }
        }

        $this->assertTrue($asserted, 'There was nested entity inside answer');
    }

    public function testGetAllPaginated()
    {
        $defaultLimit = 10;
        $this->getFactory(TestEntity::class)->count($defaultLimit + 1)->create();
        $this->getJson('/test/entities/pages', ['Range' => 'entities=0-']);
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();

        $object = json_decode($this->response->getContent());
        $this->assertEquals($defaultLimit, count($object));
    }

    public function testGetAllPaginatedWithNested()
    {
        $defaultLimit = 10;
        $this->getFactory(TestEntity::class)->count($defaultLimit + 1)->create()->each(function (TestEntity $entity) {
            $this->addRelatedEntities($entity);
        });

        $this->getJson('/test/entities/pages', ['Range' => 'entities=-10', 'with-nested' => 'testMany']);
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertEquals($defaultLimit, count($object));
        $object = current($object);

        $this->assertObjectHasAttribute('_testMany', $object);
        $this->assertEquals(5, count($object->_testMany));
        foreach ($object->_testMany as $nestedObject) {
            $this->assertObjectHasAttribute('_self', $nestedObject);
            $this->assertTrue(is_string($nestedObject->_self), '_self is a string');
            $this->assertObjectHasAttribute('entityId', $nestedObject);
            $this->assertTrue(Uuid::isValid($nestedObject->entityId));
        }
    }

    public function testGetAllPaginatedNoRangeHeader()
    {
        $this->getJson('/test/entities/pages');
        $this->assertResponseStatus(400);
    }

    public function testGetAllPaginatedInvalidRangeHeader()
    {
        $this->getJson('/test/entities/pages', ['Range' => '0-']);
        $this->assertResponseStatus(400);
    }

    public function testGetAllPaginatedSimpleRange()
    {
        $this->getFactory(TestEntity::class)->count(20)->create();
        $totalCount = TestEntity::count();
        $this->getJson('/test/entities/pages', ['Range' => 'entities=0-19']);
        $object = json_decode($this->response->getContent());
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
        $this->assertEquals(20, count($object));
        list($first, $last, $total) = $this->parseRange($this->response->headers->get('content-range'));
        $this->assertEquals($total, $totalCount);
        $this->assertEquals($first, 0);
        $this->assertEquals($last, 19);
    }

    public function testGetAllPaginatedSimpleSearch()
    {
        $resultsMock = Mockery::mock('Elasticquent\ElasticquentResultCollection');
        $resultsMock->shouldReceive('totalHits')
            ->andReturn(0); // Force not found, we don't have to mock a success, just that 'searchByQuery' is called with the right params.

        $mockModel = Mockery::mock(TestEntity::class);
        $mockModel
            ->shouldReceive('count')
            ->andReturn(10)
            ->shouldReceive('searchByQuery')
            ->with([
                'match_phrase_prefix' => [
                    '_all' => 'foobar',
                ],
            ], null, null, 10, 0)
            ->andReturn($resultsMock);

        $this->app->instance(TestEntity::class, $mockModel);

        $this->getJson('/test/entities/pages?q='.base64_encode('foobar'), ['Range' => 'entities=0-']);

        $this->assertResponseStatus(404);
    }

    public function testGetAllPaginatedComplexSearch()
    {
        $resultsMock = Mockery::mock('Elasticquent\ElasticquentResultCollection');
        $resultsMock->shouldReceive('totalHits')
            ->andReturn(0); // Force not found, we don't have to mock a success, just that 'searchByQuery' is called with the right params.

        $mockModel = Mockery::mock(TestEntity::class);
        $mockModel
            ->shouldReceive('count')
            ->andReturn(10)
            ->shouldReceive('getIndexName')
            ->andReturn('defaultIndex')
            ->shouldReceive('getTypeName')
            ->andReturn('someTypeName')
            ->shouldReceive('complexSearch')
            ->with([
                'index' => 'defaultIndex',
                'type' => 'someTypeName',
                'body' => ['query' => ['bool' => ['must' => [['match_phrase_prefix' => ['_all' => 'search term']], ['match_phrase_prefix' => ['author_id' => 'some UUID']], ['nested' => ['path' => 'tags', 'query' => ['bool' => ['must' => ['match_phrase_prefix' => ['tags.tag_id' => 'tag ID 1']]]]]], ['nested' => ['path' => 'tags', 'query' => ['bool' => ['must' => ['match_phrase_prefix' => ['tags.tag_id' => 'tag ID 2']]]]]]]]]],
            ])
            ->andReturn($resultsMock);

        $this->app->instance(TestEntity::class, $mockModel);

        $query = [
            '_all' => ['search term'],
            'authorId' => ['some UUID'],
            '_tags' => ['tagId' => ['tag ID 1', 'tag ID 2'],
            ],
        ];

        $this->getJson('/test/entities/pages?q='.base64_encode(json_encode($query)), ['Range' => 'entities=0-']);

        $this->assertResponseStatus(404);
    }

    public function testGetAllPaginatedComplexSearchMatchAll()
    {
        $results = $this->getFactory(TestEntity::class)->count(5)->make();

        $resultsMock = Mockery::mock($results);
        $resultsMock->shouldReceive('totalHits')
            ->times(3)
            ->andReturn(5);

        $mockModel = Mockery::mock(TestEntity::class);
        $mockModel
            ->shouldReceive('count')
            ->andReturn(10)
            ->shouldReceive('getIndexName')
            ->andReturn('defaultIndex')
            ->shouldReceive('getTypeName')
            ->andReturn('someTypeName')
            ->shouldReceive('complexSearch')
            ->with([
                'index' => 'defaultIndex',
                'type' => 'someTypeName',
                'body' => ['query' => ['match_all' => []]],
            ])
            ->andReturn($resultsMock);

        $this->app->instance(TestEntity::class, $mockModel);

        $query = [
            'authorId' => [''],
        ];

        $this->getJson('/test/entities/pages?q='.base64_encode(json_encode($query)), ['Range' => 'entities=0-']);

        $this->assertResponseStatus(206);
    }

    public function testPaginationBadRanges()
    {
        $this->getFactory(TestEntity::class)->count(20)->create();
        $this->getJson('/test/entities/pages', ['Range' => 'entities=19-18']);
        $this->assertResponseStatus(400);
    }

    public function testPaginationOutOfRange()
    {
        $this->getFactory(TestEntity::class)->count(10)->create();
        $totalCount = TestEntity::count();
        $this->getJson('/test/entities/pages', ['Range' => 'entities='.$totalCount.'-']);
        $this->assertResponseStatus(416);
    }

    public function testPaginationMoreThanInRepo()
    {
        $this->getFactory(TestEntity::class)->count(10)->create();
        $totalCount = TestEntity::count();
        $this->getJson('/test/entities/pages', ['Range' => 'entities='.($totalCount - 2).'-'.($totalCount + 20)]);
        $object = json_decode($this->response->getContent());
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
        $this->assertEquals(2, count($object));
        list($first, $last, $total) = $this->parseRange($this->response->headers->get('content-range'));
        $this->assertEquals($total, $totalCount);
        $this->assertEquals($first, $totalCount - 2);
        $this->assertEquals($last, $totalCount - 1);
    }

    public function testPaginationGetLast()
    {
        $this->getFactory(TestEntity::class)->count(10)->create();
        $totalCount = TestEntity::count();
        $this->getJson('/test/entities/pages', ['Range' => 'entities=-5']);
        $object = json_decode($this->response->getContent());
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
        $this->assertEquals(5, count($object));
        list($first, $last, $total) = $this->parseRange($this->response->headers->get('content-range'));
        $this->assertEquals($total, $totalCount);
        $this->assertEquals($first, $totalCount - 5);
        $this->assertEquals($last, $totalCount - 1);
    }

    protected function parseRange($header)
    {
        $splitTotal = explode('/', str_replace('entities ', '', $header));
        $total = null;
        if (isset($splitTotal[1]) && $splitTotal[1] !== '') {
            $total = $splitTotal[1];
        }
        $firstAndLast = explode('-', $splitTotal[0]);
        $first = null;
        $last = null;
        if (isset($firstAndLast[0]) && $firstAndLast[0] !== '') {
            $first = $firstAndLast[0];
        }

        if (isset($firstAndLast[1]) && $firstAndLast[1] !== '') {
            $last = $firstAndLast[1];
        }

        return [$first, $last, $total];
    }

    public function testGetOne()
    {
        $entity = $this->getFactory(TestEntity::class)->create();

        $this->getJson('/test/entities/'.$entity->entity_id);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('entityId', $object);
        $this->assertTrue(Uuid::isValid($object->entityId));

        $this->assertTrue(is_string($object->varchar), 'Varchar column type is text');
        $this->assertTrue(is_string($object->hash), 'Hash column is a hash');
        $this->assertTrue(is_int($object->integer), 'Integer column type is integer');
        $this->assertTrue(is_float($object->decimal) || is_int($object->decimal), 'Decimal column type is integer');
        $this->assertNull($object->nullable, 'Nullable column type is null');
        $this->assertTrue(is_string($object->text), 'Text column type is text');
        $this->assertValidIso8601Date($object->date, 'Date column type is a valid ISO8601 date');
        $this->assertObjectHasAttribute('multiWordColumnTitle', $object, 'Multi word colum is camel cased');

        $this->assertValidIso8601Date($object->createdAt, 'createdAt column is a valid ISO8601 date');
        $this->assertValidIso8601Date($object->updatedAt, 'updatedAt column is a valid ISO8601 date');

        $this->assertObjectNotHasAttribute('hidden', $object, 'Hidden is not hidden.');
    }

    public function testGetOneWithNested()
    {
        /** @var TestEntity $entity */
        $entity = $this->getFactory(TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $this->getJson('/test/entities/'.$entity->entity_id, ['with-nested' => 'testMany']);
        $object = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $this->assertTrue(is_object($object), 'Response is an object');
        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('_testMany', $object);
        $this->assertEquals(5, count($object->_testMany));
        foreach ($object->_testMany as $nestedObject) {
            $this->assertObjectHasAttribute('_self', $nestedObject);
            $this->assertTrue(is_string($nestedObject->_self), '_self is a string');
            $this->assertObjectHasAttribute('entityId', $nestedObject);
            $this->assertTrue(Uuid::isValid($nestedObject->entityId));
        }
    }

    public function testGetOneWithInvalidNested()
    {
        /** @var TestEntity $entity */
        $entity = $this->getFactory(TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $this->getJson('/test/entities/'.$entity->entity_id, ['with-nested' => 'not-a-valid-nesting']);

        $this->shouldReturnJson();
        $this->assertResponseStatus(400);
    }

    public function testPostOneValid()
    {
        $entity = $this->getFactory(TestEntity::class)
            ->makeVisible(['hidden'])
            ->transformed();

        $this->withAuthorization()->postJson('/test/entities', $entity);

        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);
    }

    public function testPostOneInvalid()
    {
        $entity = $this->getFactory(TestEntity::class)
            ->hide(['text'])
            ->transformed();

        $this->withAuthorization()->postJson('/test/entities', $entity);

        $object = json_decode($this->response->getContent());

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $this->assertObjectHasAttribute('text', $object->invalid);

        $this->assertEquals('The text field is required.', $object->invalid->text[0]->message);
    }

    public function testPutOneNew()
    {
        $entity = $this->getFactory(TestEntity::class)->makeVisible(['hidden'])->transformed();
        $rowCount = TestEntity::count();

        $this->withAuthorization()->putJson('/test/entities/'.$entity['entityId'], $entity);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 1, TestEntity::count());
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);
    }

    public function testPutOneCollidingIds()
    {
        $factory = $this->getFactory(TestEntity::class);
        $entity = $factory->create();
        $id = $entity->entity_id;

        $data = $factory
            ->customize(['entityId' => (string) Uuid::uuid4()])
            ->transformed();

        $this->withAuthorization()->putJson('/test/entities/'.$id, $data);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(400);
        $this->assertTrue(is_object($object));

        $this->assertObjectHasAttribute('message', $object);
        $this->assertEquals('Provided entity body does not match route parameter. The entity key cannot be updated', $object->message);
    }

    public function testPutOneNewInvalidId()
    {
        $entity = $this->getFactory(TestEntity::class)
            ->customize(['entityId' => 'foobar'])
            ->transformed();

        $this->withAuthorization()->putJson('/test/entities/'.$entity['entityId'], $entity);

        $this->shouldReturnJson();
        $this->assertResponseStatus(500);
    }

    public function testPutManyNoIds()
    {
        $entities = $this->getFactory(TestEntity::class)
            ->hide(['entity_id'])
            ->count(5)
            ->transformed();

        $this->withAuthorization()->putJson('/test/entities', $entities);
        $this->assertResponseStatus(422);
    }

    public function testPatchManyNoIds()
    {
        $factory = $this->getFactory(TestEntity::class);
        $factory->count(5)->create();

        $entities = $factory
            ->hide(['entity_id', '_self'])
            ->count(5)
            ->transformed();

        $this->withAuthorization()->patchJson('/test/entities', $entities);
        $this->assertResponseStatus(422);
    }

    public function testPutManyNew()
    {
        $entities = $this->getFactory(TestEntity::class)->count(5)->makeVisible(['hidden'])->transformed();

        $rowCount = TestEntity::count();

        $this->withAuthorization()->putJson('/test/entities', $entities);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 5, TestEntity::count());
        $this->assertTrue(is_array($object));
        $this->assertCount(5, $object);
        $this->assertStringStartsWith('http', $object[0]->_self);
    }

    public function testPutManySomeNew()
    {
        $factory = $this->getFactory(TestEntity::class);
        $factory->count(5)->create();
        $entities = $factory->makeVisible(['hidden'])->transformed();

        $entities[0]['entityId'] = (string) Uuid::uuid4();
        $entities[1]['entityId'] = (string) Uuid::uuid4();
        $rowCount = TestEntity::count();

        $this->withAuthorization()->putJson('/test/entities', $entities);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 2, TestEntity::count());
        $this->assertTrue(is_array($object));
        $this->assertCount(5, $object);
        $this->assertStringStartsWith('http', $object[0]->_self);
    }

    public function testPutManyNewInvalidId()
    {
        $entities = $this->getFactory(TestEntity::class)->count(5)
            ->makeVisible(['hidden'])
            ->customize(['entity_id' => 'foobar'])
            ->transformed();

        $rowCount = TestEntity::count();

        $this->withAuthorization()->putJson('/test/entities', $entities);

        $object = json_decode($this->response->getContent());

        $this->assertCount(5, $object->invalid);
        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertEquals('The entity id must be an UUID string.', $object->invalid[0]->entityId[0]->message);
        $this->assertEquals($rowCount, TestEntity::count());
    }

    public function testPutManyNewInvalid()
    {
        $entities = $this->getFactory(TestEntity::class)->count(5)
            ->makeVisible(['hidden'])
            ->customize(['multi_word_column_title' => 'foobar'])
            ->transformed();

        $rowCount = TestEntity::count();

        $this->withAuthorization()->putJson('/test/entities', $entities);

        $object = json_decode($this->response->getContent());

        $this->assertCount(5, $object->invalid);
        $this->assertObjectHasAttribute('multiWordColumnTitle', $object->invalid[0]);

        $this->assertEquals('The multi word column title field must be true or false.', $object->invalid[0]->multiWordColumnTitle[0]->message);
        $this->assertEquals($rowCount, TestEntity::count());
    }

    public function testPatchOne()
    {
        $entity = factory(TestEntity::class)->create();

        $this->withAuthorization()->patchJson('/test/entities/'.$entity->entity_id, ['varchar' => 'foobar']);

        $entity = TestEntity::find($entity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $entity->varchar);
    }

    public function testPatchOneInvalidId()
    {
        $this->withAuthorization()->patchJson('/test/entities/'.(string) Uuid::uuid4(), ['varchar' => 'foobar']);
        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('entityId', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entityId[0]->message);
    }

    public function testPatchMany()
    {
        $entities = factory(TestEntity::class, 5)->create();

        $data = array_map(function ($entity) {
            return [
                'entity_id' => $entity->entity_id,
                'varchar'   => 'foobar',
            ];
        }, $entities->all());

        $this->withAuthorization()->patchJson('/test/entities', $data);

        $entity = TestEntity::find($entities->random()->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $entity->varchar);
    }

    public function testPatchManyInvalidId()
    {
        $entities = factory(TestEntity::class, 5)->create();

        $data = array_map(function ($entity) {
            return [
                'entity_id' => (string) Uuid::uuid4(),
                'varchar' => 'foobar',
            ];
        }, $entities->all());

        $this->withAuthorization()->patchJson('/test/entities', $data);
        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid[0]->entityId[0]->message);
    }

    public function testPatchManyInvalid()
    {
        $entities = factory(TestEntity::class, 5)->create();

        $data = array_map(function ($entity) {
            return [
                'entity_id' => $entity->entity_id,
                'multi_word_column_title' => 'foobar',
            ];
        }, $entities->all());

        $this->withAuthorization()->patchJson('/test/entities', $data);
        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('multiWordColumnTitle', $object->invalid[0]);
        $this->assertEquals('The multi word column title field must be true or false.', $object->invalid[0]->multiWordColumnTitle[0]->message);
    }

    public function testDeleteOne()
    {
        $entity = factory(TestEntity::class)->create();
        $rowCount = TestEntity::count();

        $this->withAuthorization()->deleteJson('/test/entities/'.$entity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, TestEntity::count());
    }

    public function testDeleteOneInvalidId()
    {
        $entity = factory(TestEntity::class)->create();
        $rowCount = TestEntity::count();

        $this->withAuthorization()->deleteJson('/test/entities/'.'c4b3c8d3-fa8b-4cf6-828a-072bcf7dc371');

        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('entityId', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entityId[0]->message);
        $this->assertEquals($rowCount, TestEntity::count());
    }

    public function testDeleteMany()
    {
        $entities = factory(TestEntity::class, 5)->create()->all();
        $rowCount = TestEntity::count();

        $this->withAuthorization()->deleteJson('/test/entities', $entities);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 5, TestEntity::count());
    }

    public function testDeleteManyInvalidId()
    {
        $entities = factory(TestEntity::class, 5)->create();
        $rowCount = TestEntity::count();
        $entities->first()->entity_id = (string) Uuid::uuid4();
        $entities->last()->entity_id = (string) Uuid::uuid4();

        $this->withAuthorization()->deleteJson('/test/entities', $entities->all());

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_array($object->invalid));
        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertNull($object->invalid[1]);
        $this->assertObjectHasAttribute('entityId', $object->invalid[4]);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid[0]->entityId[0]->message);
        $this->assertEquals($rowCount, TestEntity::count());
    }

    public function testNoInnerLumenUrlDecode()
    {
        $compareString = '%foo?*:bar/"foo';
        $this->getJson('/test/entities_encoded/'.urlencode($compareString));
        $object = json_decode($this->response->getContent());
        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertEquals(urldecode($object->test), $compareString);
    }

    public function testEntitySearch()
    {
        TestEntity::removeAllFromIndex();

        $searchEntity = factory(TestEntity::class)->create([
            'varchar' => 'searchforthisstring',
        ]);

        sleep(1); //give the elastic search agent time to index

        $this->getJson('/test/entities/pages?q='.base64_encode('searchforthisstring'), ['Range' => 'entities=0-9']);

        $collection = json_decode($this->response->getContent());
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();

        $this->assertCount(1, $collection);

        $this->assertEquals($searchEntity->entity_id, $collection[0]->entityId);
    }

    public function testEntitySearchNoResults()
    {
        $this->getJson('/test/entities/pages?q='.base64_encode('thisstringwontreturnresults'), ['Range' => 'entities=0-9']);

        $this->assertResponseStatus(404);
        $this->shouldReturnJson();
        $this->assertJsonArray();
    }

    /**
     * @group localizations
     */
    public function testPutOneLocalization()
    {
        // Create an entity
        $entity = factory(TestEntity::class)->create();

        $supportedRegions = array_pluck(config('regions.supported'), 'code');
        $region = array_pop($supportedRegions);

        $localization = [
            'varchar' => 'foobar',
            'decimal' => 0.234,
        ];

        // Give entity a localization
        $this->withAuthorization()->putJson('/test/entities/'.$entity->entity_id.'/localizations/'.$region, $localization);
        $this->assertResponseStatus(201);

        // Get the saved localization
        $localizationModel = $entity->localizations->where('region_code', $region)->first();

        $savedLocalization = $localizationModel->localizations;

        // Ensure localization was saved correctly
        $this->assertEquals($localization, $savedLocalization);

        // Assert the cache
        $cachedLocalization = Localization::getFromCache($localizationModel->localizable_id, $localizationModel->region_code);

        $this->assertEquals($localization, $cachedLocalization);
    }

    /**
     * @group localizations
     */
    public function testPutOneLocalizationInvalidParameter()
    {
        $entity = factory(TestEntity::class)->create();

        $localization = [
            'foobar' => 'foobar',
            'decimal' => 0.17,
        ];

        $this->withAuthorization()->putJson('/test/entities/'.$entity->entity_id.'/localizations/au', $localization);
        $this->assertException('There was an issue with the validation of provided entity', 422, 'ValidationException');
    }

    /**
     * @group localizations
     */
    public function testGetOneLocalizationNotFound()
    {
        // Create an entity
        $entity = factory(TestEntity::class)->create();

        $localization = [
            'varchar' => 'barfoo',
            'decimal' => 0.236,
        ];

        // Give it a localization
        $entity->localizations()->create([
            'region_code' => 'au',
            'localizations' => $localization,
        ]);

        $this->getJson('/test/entities/'.$entity->entity_id.'/localizations/zz');

        $this->assertResponseStatus(404);
    }

    /**
     * @group localizations
     */
    public function testGetOneLocalization()
    {
        // Create an entity
        /** @var TestEntity $entity */
        $entity = factory(TestEntity::class)->create();

        $supportedRegions = array_pluck(config('regions.supported'), 'code');
        $region = array_pop($supportedRegions);

        $localization = [
            'varchar' => 'barfoo',
            'decimal' => 0.236,
        ];

        // Give it a localization
        $entity->localizations()->create([
            'region_code' => $region,
            'localizations' => $localization,
        ]);

        // Retrieve the localization
        $this->getJson('/test/entities/'.$entity->entity_id.'/localizations/'.$region);
        $this->assertResponseStatus(200);
        $this->shouldReturnJson();

        $response = json_decode($this->response->getContent(), true);

        $this->assertEquals($response['regionCode'], $region);
        $this->assertEquals($response['localizations'], $localization);
    }

    /**
     * @group localizations
     */
    public function testGetAllLocalizationsNotFound()
    {
        $randomId = (string) \Rhumsaa\Uuid\Uuid::uuid4();
        $this->getJson("/test/entities/$randomId/localizations");
        $this->assertResponseStatus(404);
    }

    /**
     * @group localizations
     */
    public function testGetAllLocalizations()
    {
        // Create an entity
        $entity = factory(TestEntity::class)->create();

        $supportedRegions = array_pluck(config('regions.supported'), 'code');

        $regionOne = array_pop($supportedRegions);
        $regionTwo = array_pop($supportedRegions);

        $localizationOne = [
            'varchar' => 'foofoo',
            'decimal' => 0.2,
        ];

        $localizationTwo = [
            'varchar' => 'barbar',
            'decimal' => 0.3,
        ];

        // Give it localizations
        $entity->localizations()->create([
            'region_code' => $regionOne,
            'localizations' => $localizationOne,
        ]);

        $entity->localizations()->create([
            'region_code' => $regionTwo,
            'localizations' => $localizationTwo,
        ]);

        // Retrieve the localizations
        $this->getJson('/test/entities/'.$entity->entity_id.'/localizations');
        $this->assertResponseStatus(200);
        $this->shouldReturnJson();

        $response = json_decode($this->response->getContent(), true);

        $this->assertEquals(count($response), 2);
    }

    /**
     * @group localizations
     */
    public function testShouldGetLocalizedContent()
    {
        // Create an entity
        $entity = factory(TestEntity::class)->create();

        $supportedRegions = array_pluck(config('regions.supported'), 'code');
        $region = array_pop($supportedRegions);

        $localization = [
            'varchar' => 'foofoo',
            'decimal' => 0.2,
        ];

        // Give it localizations
        $entity->localizations()->create([
            'region_code' => $region,
            'localizations' => $localization,
        ]);

        // Retrieve the entity asking for localized content
        $this->getJson('/test/entities/'.$entity->entity_id, ['accept-region' => $region]);
        $this->assertResponseStatus(200);
        $this->shouldReturnJson();

        $localizedEntity = json_decode($this->response->getContent(), true);

        $this->assertEquals($localization['varchar'], $localizedEntity['varchar']);
        $this->assertEquals($localization['decimal'], $localizedEntity['decimal']);
    }

    /**
     * @group localizations
     */
    public function testShouldGetDeepLocalizedContent()
    {
        // Create an entity
        $entity = factory(TestEntity::class)->create();

        $entity->json = [
            'some' => 'original value',
            'another' => [
                [
                    'deeper' => 'original value',
                ],
            ],
        ];

        $entity->save();

        $supportedRegions = array_pluck(config('regions.supported'), 'code');
        $region = array_pop($supportedRegions);

        $localization = [
            'json' => [
                'some' => 'localization',
                'another' => [
                    [
                        'deeper' => 'localization',
                    ],
                ],
            ],
        ];

        // Give it localizations
        $entity->localizations()->create([
            'region_code' => $region,
            'localizations' => $localization,
        ]);

        // Retrieve the entity asking for localized content
        $this->getJson('/test/entities/'.$entity->entity_id, ['accept-region' => $region]);
        $this->assertResponseStatus(200);
        $this->shouldReturnJson();

        $localizedEntity = json_decode($this->response->getContent(), true);

        $this->assertEquals('localization', $localizedEntity['json']['some']);
        $this->assertEquals('localization', $localizedEntity['json']['another'][0]['deeper']);
    }
}
