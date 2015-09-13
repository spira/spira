<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\TestEntity;
use Rhumsaa\Uuid\Uuid;

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
        App\Models\TestEntity::flushEventListeners();
        App\Models\TestEntity::boot();
    }

    /**
     * Prepare a factory generated entity to be sent as input data.
     *
     * @param Arrayable $entity
     *
     * @return array
     */
    protected function prepareEntity($entity)
    {
        // We run the entity through the transformer to get the attributes named
        // as if they came from the frontend.
        $transformer = $this->app->make(\App\Http\Transformers\EloquentModelTransformer::class);
        $entity = $transformer->transform($entity);

        // As the hidden attribute is hidden when we get the array, we need to
        // add it back so it's part of the request.
        $entity['hidden'] = true;

        // And get a reformatted date
        $entity['date'] = Carbon\Carbon::createFromFormat('Y-m-d', substr($entity['date'], 0, 10))->toDateString();

        return $entity;
    }

    protected function addRelatedEntities($model)
    {
        $model->testMany = factory(App\Models\SecondTestEntity::class, 5)->make()->all();
        $model->push();
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
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $this->getJson('/test/entities', ['with-nested' => 'testMany']);
        $objects = json_decode($this->response->getContent());

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
                    $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $nestedObject->entityId);
                    $this->assertTrue(strlen($nestedObject->entityId) === 36, 'UUID has 36 chars');
                }
            }
        }

        $this->assertTrue($asserted, 'There was nested entity inside answer');
    }

    public function testGetAllPaginated()
    {
        $defaultLimit = 10;
        $entities = factory(App\Models\TestEntity::class, $defaultLimit + 1)->create();
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
        $entities = factory(App\Models\TestEntity::class, $defaultLimit + 1)->create();
        foreach ($entities as $entity) {
            $this->addRelatedEntities($entity);
        }

        $this->getJson('/test/entities/pages', ['Range' => 'entities=-10','with-nested' => 'testMany']);
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
            $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $nestedObject->entityId);
            $this->assertTrue(strlen($nestedObject->entityId) === 36, 'UUID has 36 chars');
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
        $entities = factory(App\Models\TestEntity::class, 20)->create();
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

    public function testPaginationBadRanges()
    {
        $entities = factory(App\Models\TestEntity::class, 20)->create();
        $this->getJson('/test/entities/pages', ['Range' => 'entities=19-18']);
        $this->assertResponseStatus(400);
    }

    public function testPaginationOutOfRange()
    {
        $entities = factory(App\Models\TestEntity::class, 10)->create();
        $totalCount = TestEntity::count();
        $this->getJson('/test/entities/pages', ['Range' => 'entities='.$totalCount.'-']);
        $this->assertResponseStatus(416);
    }

    public function testPaginationMoreThanInRepo()
    {
        $entities = factory(App\Models\TestEntity::class, 10)->create();
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
        $entities = factory(App\Models\TestEntity::class, 10)->create();
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

        return [$first,$last,$total];
    }

    public function testGetOne()
    {
        $entity = factory(App\Models\TestEntity::class)->create();

        $this->getJson('/test/entities/'.$entity->entity_id);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('entityId', $object);
        $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $object->entityId);
        $this->assertTrue(strlen($object->entityId) === 36, 'UUID has 36 chars');

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
        $entity = factory(App\Models\TestEntity::class)->create();
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
            $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $nestedObject->entityId);
            $this->assertTrue(strlen($nestedObject->entityId) === 36, 'UUID has 36 chars');
        }
    }

    public function testGetOneWithInvalidNested()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $this->getJson('/test/entities/'.$entity->entity_id, ['with-nested' => 'not-a-valid-nesting']);
        $object = json_decode($this->response->getContent());

        $this->shouldReturnJson();
        $this->assertResponseStatus(400);
    }

    public function testPostOneValid()
    {
        $entity = factory(App\Models\TestEntity::class)->make();

        $this->post('/test/entities', $this->prepareEntity($entity));

        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);
    }

    public function testPostOneInvalid()
    {
        $entity = factory(App\Models\TestEntity::class)->make();
        $entity = $this->prepareEntity($entity);
        unset($entity['text']);

        $this->post('/test/entities', $entity);

        $object = json_decode($this->response->getContent());

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $this->assertObjectHasAttribute('text', $object->invalid);

        $this->assertEquals('The text field is required.', $object->invalid->text[0]->message);
    }

    public function testPutOneNew()
    {
        $entity = factory(App\Models\TestEntity::class)->make();
        $id = $entity->entity_id;
        $entity = $this->prepareEntity($entity);

        $rowCount = TestEntity::count();

        $this->putJson('/test/entities/'.$id, $entity);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 1, TestEntity::count());
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);
    }

    public function testPutOneCollidingIds()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $id = $entity->entity_id;
        $entity = $this->prepareEntity($entity);
        $entity['entityId'] = (string) Uuid::uuid4();

        $this->putJson('/test/entities/'.$id, $entity);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(400);
        $this->assertTrue(is_object($object));

        $this->assertObjectHasAttribute('message', $object);
        $this->assertEquals('Provided entity body does not match route parameter. The entity key cannot be updated', $object->message);
    }

    public function testPutOneNewInvalidId()
    {
        $id = 'foobar';
        $entity = factory(App\Models\TestEntity::class)->make([
            'entity_id' => $id,
        ]);
        $entity = $this->prepareEntity($entity);

        $this->putJson('/test/entities/'.$id, $entity);

        $object = json_decode($this->response->getContent());
        $this->shouldReturnJson();
        $this->assertResponseStatus(500);
    }

    public function testPutManyNoIds()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->make();
        $entities = array_map(function ($entity) {
            return $this->prepareEntity($entity);
        }, $entities->all());
        foreach ($entities as &$entity) {
            unset($entity['entityId']);
            unset($entity['_self']);
        }

        $this->putJson('/test/entities', $entities);
        $this->assertResponseStatus(422);
    }

    public function testPatchManyNoIds()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();

        $data = array_map(function ($entity) {
            return [
                'varchar'   => 'foobar',
            ];
        }, $entities->all());

        $this->patchJson('/test/entities', $data);
        $this->assertResponseStatus(422);
    }

    public function testPutManyNew()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->make();

        $entities = array_map(function ($entity) {
            return array_add($this->prepareEntity($entity), 'entity_id', (string) Uuid::uuid4());
        }, $entities->all());

        $rowCount = TestEntity::count();

        $this->putJson('/test/entities', $entities);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 5, TestEntity::count());
        $this->assertTrue(is_array($object));
        $this->assertCount(5, $object);
        $this->assertStringStartsWith('http', $object[0]->_self);
    }

    public function testPutManySomeNew()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();

        $entities = array_map(function ($entity) {
            return $this->prepareEntity($entity);
        }, $entities->all());
        $entities[0]['entityId'] = (string) Uuid::uuid4();
        $entities[1]['entityId'] = (string) Uuid::uuid4();
        $rowCount = TestEntity::count();

        $this->putJson('/test/entities', $entities);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 2, TestEntity::count());
        $this->assertTrue(is_array($object));
        $this->assertCount(5, $object);
        $this->assertStringStartsWith('http', $object[0]->_self);
    }

    public function testPutManyNewInvalidId()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->make();

        $entities = array_map(function ($entity) {
            return array_add($this->prepareEntity($entity), 'entity_id', 'foobar');
        }, $entities->all());

        $rowCount = TestEntity::count();

        $this->putJson('/test/entities', $entities);

        $object = json_decode($this->response->getContent());

        $this->assertCount(5, $object->invalid);
        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertEquals('The entity id must be an UUID string.', $object->invalid[0]->entityId[0]->message);
        $this->assertEquals($rowCount, TestEntity::count());
    }

    public function testPutManyNewInvalid()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->make();

        $entities = array_map(function ($entity) {
            return array_add($this->prepareEntity($entity), 'multi_word_column_title', 'foobar');
        }, $entities->all());

        $rowCount = TestEntity::count();

        $this->putJson('/test/entities', $entities);

        $object = json_decode($this->response->getContent());

        $this->assertCount(5, $object->invalid);
        $this->assertObjectHasAttribute('multiWordColumnTitle', $object->invalid[0]);

        $this->assertEquals('The multi word column title field must be true or false.', $object->invalid[0]->multiWordColumnTitle[0]->message);
        $this->assertEquals($rowCount, TestEntity::count());
    }

    public function testPatchOne()
    {
        $entity = factory(App\Models\TestEntity::class)->create();

        $this->patchJson('/test/entities/'.$entity->entity_id, ['varchar' => 'foobar']);

        $entity = TestEntity::find($entity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $entity->varchar);
    }

    public function testPatchOneInvalidId()
    {
        $this->patchJson('/test/entities/'.(string) Uuid::uuid4(), ['varchar' => 'foobar']);
        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('entityId', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entityId[0]->message);
    }

    public function testPatchMany()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();

        $data = array_map(function ($entity) {
            return [
                'entity_id' => $entity->entity_id,
                'varchar'   => 'foobar',
            ];
        }, $entities->all());

        $this->patchJson('/test/entities', $data);

        $entity = TestEntity::find($entities->random()->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $entity->varchar);
    }

    public function testPatchManyInvalidId()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();

        $data = array_map(function ($entity) {
            return [
                'entity_id' => (string) Uuid::uuid4(),
                'varchar' => 'foobar',
            ];
        }, $entities->all());

        $this->patchJson('/test/entities', $data);
        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid[0]->entityId[0]->message);
    }

    public function testPatchManyInvalid()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();

        $data = array_map(function ($entity) {
            return [
                'entity_id' => $entity->entity_id,
                'multi_word_column_title' => 'foobar',
            ];
        }, $entities->all());

        $this->patchJson('/test/entities', $data);
        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('multiWordColumnTitle', $object->invalid[0]);
        $this->assertEquals('The multi word column title field must be true or false.', $object->invalid[0]->multiWordColumnTitle[0]->message);
    }

    public function testDeleteOne()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $rowCount = TestEntity::count();

        $this->deleteJson('/test/entities/'.$entity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, TestEntity::count());
    }

    public function testDeleteOneInvalidId()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $rowCount = TestEntity::count();

        $this->deleteJson('/test/entities/'.'c4b3c8d3-fa8b-4cf6-828a-072bcf7dc371');

        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('entityId', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entityId[0]->message);
        $this->assertEquals($rowCount, TestEntity::count());
    }

    public function testDeleteMany()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create()->all();
        $rowCount = TestEntity::count();

        $this->deleteJson('/test/entities', $entities);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 5, TestEntity::count());
    }

    public function testDeleteManyInvalidId()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();
        $rowCount = TestEntity::count();
        $entities->first()->entity_id = (string) Uuid::uuid4();
        $entities->last()->entity_id = (string) Uuid::uuid4();

        $this->deleteJson('/test/entities', $entities->all());

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

        $searchEntity = factory(App\Models\TestEntity::class)->create([
            'varchar' => 'searchforthisstring',
        ]);

        sleep(1); //give the elastic search agent time to index

        $this->getJson('/test/entities/pages?q=searchforthisstring', ['Range' => 'entities=0-9']);

        $collection = json_decode($this->response->getContent());
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();

        $this->assertCount(1, $collection);

        $this->assertEquals($searchEntity->entity_id, $collection[0]->entityId);
    }

    public function testEntitySearchNoResults()
    {
        $this->getJson('/test/entities/pages?q=thisstringwontreturnresults', ['Range' => 'entities=0-9']);

        $this->assertResponseStatus(404);
        $this->shouldReturnJson();
        $this->assertJsonArray();
    }

    public function testLocalisedPutOneNew()
    {
        $entity = factory(App\Models\TestEntity::class)->make();

        $locale = 'au';
        $id = $entity->entity_id;
        $entity = $this->prepareEntity($entity);

        $rowCount = TestEntity::count();

        $this->putJson('/test/entities/'.$id, $entity, ['Content-Region' => $locale]);

        // Assert the cache
        $key = sprintf('l10n:%s:%s', $id, $locale);
        $cached = json_decode(Cache::get($key), true);

        $this->assertEquals($entity['varchar'], $cached['varchar']);
        $this->assertEquals($entity['text'], $cached['text']);
    }

    public function testLocalisedPutManySomeLocalised()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();
        $locale = 'au';

        $entities = array_map(function ($entity) {
            return $this->prepareEntity($entity);
        }, $entities->all());
        $entities[1]['text'] = 'localised text';
        $entities[2]['text'] = 'localised text';

        $this->putJson('/test/entities', $entities, ['Content-Region' => $locale]);

        // Assert the cache
        $key1 = sprintf('l10n:%s:%s', $entities[1]['entityId'], $locale);
        $key2 = sprintf('l10n:%s:%s', $entities[2]['entityId'], $locale);
        $cached1 = json_decode(Cache::get($key1), true);
        $cached2 = json_decode(Cache::get($key2), true);

        $this->assertEquals($entities[1]['text'], $cached1['text']);
        $this->assertEquals($entities[2]['text'], $cached2['text']);
    }
}
