<?php

use Rhumsaa\Uuid\Uuid;

/**
 * @property \App\Repositories\BaseRepository $repository
 */
class ChildEntityTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        App\Models\TestEntity::flushEventListeners();
        App\Models\TestEntity::boot();
        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
        App\Models\SecondTestEntity::flushEventListeners();
        App\Models\SecondTestEntity::boot();

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
        $transformer = $this->app->make(\App\Http\Transformers\EloquentModelTransformer::class);
        $entity = $transformer->transform($entity);
        return $entity;
    }

    protected function addRelatedEntities($model)
    {
        $model->testMany = factory(App\Models\SecondTestEntity::class, 5)->make()->all();
        $this->repository->save($model);
    }

    public function testGetAll()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $this->get('/test/entities/'.$entity->entity_id.'/children');

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
    }

    public function testGetOne()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childEntity = $entity->testMany->first();

        $this->get('/test/entities/'.$entity->entity_id.'/child/'.$childEntity->entity_id);
        $object = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('entityId', $object);
        $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $object->entityId);
        $this->assertTrue(strlen($object->entityId) === 36, 'UUID has 36 chars');
        $this->assertTrue(is_string($object->value), 'Varchar column type is text');

        $this->assertEquals($childEntity->entity_id,$object->entityId);

    }

    public function testPostOneValid()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childEntity = factory(App\Models\SecondTestEntity::class)->make();

        $this->post('/test/entities/'.$entity->entity_id.'/child', $this->prepareEntity($childEntity));

        $this->shouldReturnJson();
        $this->assertResponseStatus(201);
    }

    public function testPostOneInvalid()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childEntity = factory(App\Models\SecondTestEntity::class)->make();
        $childEntity = $this->prepareEntity($childEntity);
        unset($childEntity['value']);

        $this->post('/test/entities/'.$entity->entity_id.'/child', $childEntity);

        $object = json_decode($this->response->getContent());

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $this->assertObjectHasAttribute('value', $object->invalid);
        $this->assertEquals('The value field is required.', $object->invalid->value[0]->message);
    }

    public function testPutOneNew()
    {
        $this->markTestSkipped();
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
        $this->markTestSkipped();
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
        $this->markTestSkipped();
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
        $this->markTestSkipped();
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
        $this->markTestSkipped();
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
        $this->markTestSkipped();
        $entity = factory(App\Models\TestEntity::class)->create();

        $this->patch('/test/entities/'.$entity->entity_id, ['varchar' => 'foobar']);

        $entity = $this->repository->find($entity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $entity->varchar);
    }

    public function testPatchOneInvalidId()
    {
        $this->markTestSkipped();
        $this->patch('/test/entities/'.(string) Uuid::uuid4(), ['varchar' => 'foobar']);
        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('entityId', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entityId[0]->message);
    }

    public function testPatchMany()
    {
        $this->markTestSkipped();
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
        $this->markTestSkipped();
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
        $this->markTestSkipped();
        $entity = factory(App\Models\TestEntity::class)->create();
        $rowCount = $this->repository->count();

        $this->delete('/test/entities/'.$entity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, $this->repository->count());
    }

    public function testDeleteOneInvalidId()
    {
        $this->markTestSkipped();
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
        $this->markTestSkipped();
        $entities = factory(App\Models\TestEntity::class, 5)->create()->all();
        $rowCount = $this->repository->count();

        $this->delete('/test/entities', ['data' => $entities]);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 5, $this->repository->count());
    }

    public function testDeleteManyInvalidId()
    {
        $this->markTestSkipped();
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
