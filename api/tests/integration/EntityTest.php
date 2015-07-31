<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Rhumsaa\Uuid\Uuid;

/**
 * @property \App\Repositories\BaseRepository $repository
 */
class EntityTest extends TestCase
{
    use DatabaseTransactions;

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

        // Get a repository instance, for assertions
        $this->repository = $this->app->make('App\Repositories\TestRepository');
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
        $transformer = $this->app->make(\App\Http\Transformers\IlluminateModelTransformer::class);
        $entity = $transformer->transform($entity);

        // As the hidden attribute is hidden when we get the array, we need to
        // add it back so it's part of the request.
        $entity['hidden'] = true;

        // And get a reformatted date
        $entity['date'] = Carbon\Carbon::createFromFormat('Y-m-d', substr($entity['date'], 0, 10))->toDateString();

        return $entity;
    }

    public function testGetAll()
    {
        $this->get('/test/entities');

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
    }

    public function testGetAllPaginated()
    {
        $defaultLimit = 10;
        $entities = factory(App\Models\TestEntity::class, $defaultLimit+1)->create();
        $this->get('/test/entities/pages',['Range'=>'0-']);
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();

        $object = json_decode($this->response->getContent());
        $this->assertEquals($defaultLimit, count($object));
    }

    public function testGetAllPaginatedNoRangeHeader()
    {
        $this->get('/test/entities/pages');
        $this->assertResponseStatus(400);
    }

    public function testGetAllPaginatedSimpleRange()
    {
        $entities = factory(App\Models\TestEntity::class, 20)->create();
        $totalCount = $this->repository->count();
        $this->get('/test/entities/pages', ['Range'=>'0-19']);
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
        $this->get('/test/entities/pages', ['Range'=>'19-18']);
        $this->assertResponseStatus(416);
    }

    public function testPaginationOutOfRange()
    {
        $entities = factory(App\Models\TestEntity::class, 10)->create();
        $totalCount = $this->repository->count();
        $this->get('/test/entities/pages', ['Range'=>$totalCount.'-']);
        $this->assertResponseStatus(416);
    }

    public function testPaginationMoreThanInRepo()
    {
        $entities = factory(App\Models\TestEntity::class, 10)->create();
        $totalCount = $this->repository->count();
        $this->get('/test/entities/pages', ['Range'=>($totalCount-2).'-'.($totalCount+20)]);
        $object = json_decode($this->response->getContent());
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
        $this->assertEquals(2, count($object));
        list($first, $last, $total) = $this->parseRange($this->response->headers->get('content-range'));
        $this->assertEquals($total, $totalCount);
        $this->assertEquals($first, $totalCount-2);
        $this->assertEquals($last, $totalCount-1);
    }

    public function testPaginationGetLast()
    {
        $entities = factory(App\Models\TestEntity::class, 10)->create();
        $totalCount = $this->repository->count();
        $this->get('/test/entities/pages', ['Range'=>'-5']);
        $object = json_decode($this->response->getContent());
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
        $this->assertEquals(5, count($object));
        list($first, $last, $total) = $this->parseRange($this->response->headers->get('content-range'));
        $this->assertEquals($total, $totalCount);
        $this->assertEquals($first, $totalCount-5);
        $this->assertEquals($last, $totalCount-1);
    }

    protected function parseRange($header)
    {
        $splitTotal = explode('/', $header);
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

        $this->get('/test/entities/'.$entity->entity_id);

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
        $this->assertTrue(is_integer($object->integer), 'Integer column type is integer');
        $this->assertTrue(is_float($object->decimal), 'Decimal column type is integer');
        $this->assertNull($object->nullable, 'Nullable column type is null');
        $this->assertTrue(is_string($object->text), 'Text column type is text');
        $this->assertValidIso8601Date($object->date, 'Date column type is a valid ISO8601 date');
        $this->assertObjectHasAttribute('multiWordColumnTitle', $object, 'Multi word colum is camel cased');

        $this->assertValidIso8601Date($object->createdAt, 'createdAt column is a valid ISO8601 date');
        $this->assertValidIso8601Date($object->updatedAt, 'updatedAt column is a valid ISO8601 date');

        $this->assertObjectNotHasAttribute('hidden', $object, 'Hidden is not hidden.');
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

        $rowCount = $this->repository->count();

        $this->put('/test/entities/'.$id, $entity);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 1, $this->repository->count());
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);
    }

    public function testPutOneCollidingIds()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $id = $entity->entity_id;
        $entity = $this->prepareEntity($entity);
        $entity['entityId'] = (string) Uuid::uuid4();

        $this->put('/test/entities/'.$id, $entity);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(422);
        $this->assertTrue(is_object($object));

        $this->assertEquals('The entity id can not be changed.', $object->invalid->entityId[0]->message);
    }

    public function testPutOneNewInvalidId()
    {
        $id = 'foobar';
        $entity = factory(App\Models\TestEntity::class)->make([
            'entity_id' => $id,
        ]);
        $entity = $this->prepareEntity($entity);

        $this->put('/test/entities/'.$id, $entity);

        $object = json_decode($this->response->getContent());
        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $this->assertObjectHasAttribute('entityId', $object->invalid);
        $this->assertEquals('The entity id must be an UUID string.', $object->invalid->entityId[0]->message);
    }

    public function testPutManyNew()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->make();

        $entities = array_map(function ($entity) {
            return array_add($this->prepareEntity($entity), 'entity_id', (string) Uuid::uuid4());
        }, $entities->all());

        $rowCount = $this->repository->count();

        $this->put('/test/entities', ['data' => $entities]);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 5, $this->repository->count());
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

        $rowCount = $this->repository->count();

        $this->put('/test/entities', ['data' => $entities]);

        $object = json_decode($this->response->getContent());

        $this->assertCount(5, $object->invalid);
        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertEquals('The entity id must be an UUID string.', $object->invalid[0]->entityId[0]->message);
        $this->assertEquals($rowCount, $this->repository->count());
    }

    public function testPatchOne()
    {
        $entity = factory(App\Models\TestEntity::class)->create();

        $this->patch('/test/entities/'.$entity->entity_id, ['varchar' => 'foobar']);

        $entity = $this->repository->find($entity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $entity->varchar);
    }

    public function testPatchOneInvalidId()
    {
        $this->patch('/test/entities/'.(string) Uuid::uuid4(), ['varchar' => 'foobar']);
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

        $this->patch('/test/entities', ['data' => $data]);

        $entity = $this->repository->find($entities->random()->entity_id);

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
                'varchar' => 'foobar'
            ];
        }, $entities->all());

        $this->patch('/test/entities', ['data' => $data]);
        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid[0]->entityId[0]->message);
    }

    public function testDeleteOne()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $rowCount = $this->repository->count();

        $this->delete('/test/entities/'.$entity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, $this->repository->count());
    }

    public function testDeleteOneInvalidId()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $rowCount = $this->repository->count();

        $this->delete('/test/entities/'.'c4b3c8d3-fa8b-4cf6-828a-072bcf7dc371');

        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('entityId', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entityId[0]->message);
        $this->assertEquals($rowCount, $this->repository->count());
    }

    public function testDeleteMany()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create()->all();
        $rowCount = $this->repository->count();

        $this->delete('/test/entities', ['data' => $entities]);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 5, $this->repository->count());
    }

    public function testDeleteManyInvalidId()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();
        $rowCount = $this->repository->count();
        $entities->first()->entity_id = (string) Uuid::uuid4();
        $entities->last()->entity_id = (string) Uuid::uuid4();

        $this->delete('/test/entities', ['data' => $entities]);

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_array($object->invalid));
        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertNull($object->invalid[1]);
        $this->assertObjectHasAttribute('entityId', $object->invalid[4]);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid[0]->entityId[0]->message);
        $this->assertEquals($rowCount, $this->repository->count());
    }
}
