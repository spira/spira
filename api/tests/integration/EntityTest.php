<?php

use Rhumsaa\Uuid\Uuid;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
     * @param  Arrayable  $entity
     * @return array
     */
    protected function prepareEntity($entity)
    {
        // We run the entity through the transformer to get the attributes named
        // as if they came from the frontend.
        $transformer = $this->app->make('App\Http\Transformers\BaseTransformer');
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
        $this->assertTrue(is_array($object));
        $this->assertStringStartsWith('http', $object[0]);
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
        $entity = $this->prepareEntity($entity);
        $id = (string) Uuid::uuid4();

        $rowCount = $this->repository->count();

        $this->put('/test/entities/'.$id, $entity);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 1, $this->repository->count());
        $this->assertTrue(is_array($object));
        $this->assertStringStartsWith('http', $object[0]);
    }

    public function testPutOneCollidingIds()
    {
        $entity = factory(App\Models\TestEntity::class)->make();
        $entity = $this->prepareEntity($entity);
        $id = (string) Uuid::uuid4();
        $entity['entityId'] = (string) Uuid::uuid4();

        $rowCount = $this->repository->count();

        $this->put('/test/entities/'.$id, $entity);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(422);
        $this->assertTrue(is_object($object));
        $this->assertEquals('The existing ID should not be overwritten.', $object->invalid->entity_id[0]);
    }

    public function testPutOneNewInvalidId()
    {
        $entity = factory(App\Models\TestEntity::class)->make();
        $entity = $this->prepareEntity($entity);
        $id = 'foobar';

        $this->put('/test/entities/'.$id, $entity);

        $object = json_decode($this->response->getContent());

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $this->assertObjectHasAttribute('entity_id', $object->invalid);
        $this->assertEquals('The entity id must be an UUID string.', $object->invalid->entity_id[0]->message);
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
        $this->assertStringStartsWith('http', $object[0]);
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
        $this->assertObjectHasAttribute('entity_id', $object->invalid);
        $this->assertEquals('The entity id must be an UUID string.', $object->invalid->entity_id[0]->message);
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
        $entity = factory(App\Models\TestEntity::class)->create();

        $this->patch('/test/entities/'.(string) Uuid::uuid4(), ['varchar' => 'foobar']);
        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('entity_id', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entity_id[0]->message);
    }

    public function testPatchMany()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();

        $data = array_map(function ($entity) {
            return [
                'entity_id' => $entity->entity_id,
                'varchar' => 'foobar'
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

        $this->assertObjectHasAttribute('entity_id', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entity_id[0]->message);
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

        $this->assertObjectHasAttribute('entity_id', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entity_id[0]->message);
        $this->assertEquals($rowCount, $this->repository->count());
    }

    public function testDeleteMany()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();
        $rowCount = $this->repository->count();
        $ids = $entities->lists('entity_id')->toArray();

        $this->delete('/test/entities', ['data' => $ids]);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 5, $this->repository->count());
    }

    public function testDeleteManyInvalidId()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();
        $rowCount = $this->repository->count();

        $this->delete('/test/entities', ['data' => [(string) Uuid::uuid4()]]);

        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('entity_id', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entity_id[0]->message);
        $this->assertEquals($rowCount, $this->repository->count());
    }
}
