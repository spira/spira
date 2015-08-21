<?php

use App\Models\TestEntity;
use Rhumsaa\Uuid\Uuid;
use Spira\Model\Collection\Collection;

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

    protected function addRelatedEntities(\Spira\Model\Model\BaseModel $model)
    {
        $model->testMany = factory(App\Models\SecondTestEntity::class, 5)->make()->all();
        $model->push();
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

    public function testGetOneNotFoundParent()
    {
        $this->get('/test/entities/'.Uuid::uuid4().'/child/'.Uuid::uuid4());
        $this->assertResponseStatus(422);
        $this->shouldReturnJson();
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

        $this->assertEquals($childEntity->entity_id, $object->entityId);
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
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childEntity = factory(App\Models\SecondTestEntity::class)->make();
        $childEntity = $this->prepareEntity($childEntity);

        $rowCount = TestEntity::find($entity->entity_id)->testMany->count();

        $this->put('/test/entities/'.$entity->entity_id.'/child/'.$childEntity['entityId'], $childEntity);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 1, TestEntity::find($entity->entity_id)->testMany->count());
        $this->assertTrue(is_object($object));
    }

    public function testPutOneCollidingIds()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childEntity = $entity->testMany->first();
        $childEntity = $this->prepareEntity($childEntity);
        $prevId = $childEntity['entityId'];
        $childEntity['entityId'] = (string) Uuid::uuid4();

        $this->put('/test/entities/'.$entity->entity_id.'/child/'.$prevId, $childEntity);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(400);
        $this->assertTrue(is_object($object));

        $this->assertObjectHasAttribute('message', $object);
        $this->assertEquals("Provided entity body does not match route parameter. The entity key cannot be updated", $object->message);
    }

    public function testPutOneNewInvalidId()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childEntity = factory(App\Models\SecondTestEntity::class)->make();
        $childEntity = $this->prepareEntity($childEntity);
        $childEntity['entityId'] = 'foobar';

        $this->put('/test/entities/'.$entity->entity_id.'/child/'.$childEntity['entityId'], $childEntity);

        $object = json_decode($this->response->getContent());
        $this->shouldReturnJson();
        $this->assertResponseStatus(500);
    }

    public function testPutManyNoIds()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $childEntities = factory(App\Models\SecondTestEntity::class, 5)->make();
        $childEntities = array_map(function ($entity) {
            return $this->prepareEntity($entity);
        }, $childEntities->all());
        foreach ($childEntities as &$childEntity) {
            unset($childEntity['entityId']);
            unset($childEntity['_self']);
        }

        $this->put('/test/entities/'.$entity->entity_id.'/children', ['data' => $childEntities]);
        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
    }

    public function testPatchManyNoIds()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $childEntities = $entity->testMany;
        $data = array_map(function ($entity) {
            return [
                'value'   => 'foobar',
            ];
        }, $childEntities->all());

        $this->patch('/test/entities/'.$entity->entity_id.'/children', ['data' => $data]);
        $this->assertResponseStatus(422);
    }

    public function testPutManyNew()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $childEntities = factory(App\Models\SecondTestEntity::class, 5)->make();
        $childEntities = array_map(function ($entity) {
            return $this->prepareEntity($entity);
        }, $childEntities->all());

        $childCount = TestEntity::find($entity->entity_id)->testMany->count();

        $this->put('/test/entities/'.$entity->entity_id.'/children', ['data' => $childEntities]);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($childCount + 5, TestEntity::find($entity->entity_id)->testMany->count());
        $this->assertTrue(is_array($object));
        $this->assertCount(5, $object);
    }

    public function testPutManyNewInvalidId()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $childEntities = factory(App\Models\SecondTestEntity::class, 5)->make();
        $childEntities = array_map(function ($entity) {
            return array_add($this->prepareEntity($entity), 'entity_id', 'foobar');
        }, $childEntities->all());

        $childCount = TestEntity::find($entity->entity_id)->testMany->count();

        $this->put('/test/entities/'.$entity->entity_id.'/children', ['data' => $childEntities]);

        $object = json_decode($this->response->getContent());

        $this->assertCount(5, $object->invalid);
        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertEquals('The entity id must be an UUID string.', $object->invalid[0]->entityId[0]->message);
        $this->assertEquals($childCount, TestEntity::find($entity->entity_id)->testMany->count());
    }

    public function testPutManyNewInvalid()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $childEntities = factory(App\Models\SecondTestEntity::class, 5)->make();
        $childEntities = array_map(function ($entity) {
            return $this->prepareEntity($entity);
        }, $childEntities->all());

        foreach ($childEntities as &$childEntity) {
            unset($childEntity['value']);
        }

        $rowCount = TestEntity::count();

        $this->put('/test/entities/'.$entity->entity_id.'/children', ['data' => $childEntities]);

        $object = json_decode($this->response->getContent());
        $this->assertCount(5, $object->invalid);
        $this->assertObjectHasAttribute('value', $object->invalid[0]);
        $this->assertEquals($rowCount, TestEntity::count());
    }

    public function testPatchOne()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childEntity = $entity->testMany->first();

        $this->patch('/test/entities/'.$entity->entity_id.'/child/'.$childEntity->entity_id, ['value' => 'foobar']);

        $entity = TestEntity::find($entity->entity_id);
        /** @var Collection $childEntities */
        $childEntities = $entity->testMany;
        $childEntity = $childEntities->find($childEntity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $childEntity->value);
    }

    public function testPatchOneInvalidId()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $this->patch('/test/entities/'.$entity->entity_id.'/child/'.(string) Uuid::uuid4(), ['varchar' => 'foobar']);
        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('entityId', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entityId[0]->message);
    }

    public function testPatchMany()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $childEntities = $entity->testMany;
        $data = array_map(function ($entity) {
            return [
                'entityId' => $entity->entity_id,
                'value'   => 'foobar',
            ];
        }, $childEntities->all());

        $this->patch('/test/entities/'.$entity->entity_id.'/children', ['data' => $data]);

        $entity = TestEntity::find($entity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        foreach ($entity->testMany as $childEntity) {
            $this->assertEquals('foobar', $childEntity->value);
        }
    }

    public function testPatchManyInvalidId()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $childEntities = $entity->testMany;
        $data = array_map(function ($entity) {
            return [
                'entityId' => (string) Uuid::uuid4(),
                'value'   => 'foobar',
            ];
        }, $childEntities->all());

        $this->patch('/test/entities/'.$entity->entity_id.'/children', ['data' => $data]);
        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid[0]->entityId[0]->message);
    }

    public function testPatchManyNewInvalid()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);

        $childEntities = $entity->testMany;
        $data = array_map(function ($entity) {
            return [
                'entityId' => $entity->entity_id,
                'value'   => null,
            ];
        }, $childEntities->all());

        $this->patch('/test/entities/'.$entity->entity_id.'/children', ['data' => $data]);
        $object = json_decode($this->response->getContent());

        $this->assertCount(5, $object->invalid);
        $this->assertObjectHasAttribute('value', $object->invalid[0]);
    }

    public function testDeleteOne()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childEntity = $entity->testMany->first();
        $childCount = TestEntity::find($entity->entity_id)->testMany->count();

        $this->delete('/test/entities/'.$entity->entity_id.'/child/'.$childEntity->entity_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($childCount - 1, TestEntity::find($entity->entity_id)->testMany->count());
    }

    public function testDeleteOneInvalidId()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childCount = TestEntity::find($entity->entity_id)->testMany->count();

        $this->delete('/test/entities/'.$entity->entity_id.'/child/'.(string) Uuid::uuid4());

        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('entityId', $object->invalid);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid->entityId[0]->message);
        $this->assertEquals($childCount, TestEntity::find($entity->entity_id)->testMany->count());
    }

    public function testDeleteMany()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childCount = TestEntity::find($entity->entity_id)->testMany->count();

        $childEntities = $entity->testMany;
        $data = array_map(function ($entity) {
            return [
                'entityId' => $entity->entity_id,
                'value'   => 'foobar',
            ];
        }, $childEntities->all());

        $this->delete('/test/entities/'.$entity->entity_id.'/children', ['data' => $data]);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($childCount - 5, TestEntity::find($entity->entity_id)->testMany->count());
    }

    public function testDeleteManyInvalidId()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $this->addRelatedEntities($entity);
        $childCount = TestEntity::find($entity->entity_id)->testMany->count();
        $childEntities = $entity->testMany;

        $childEntities->first()->entity_id = (string) Uuid::uuid4();
        $childEntities->last()->entity_id = (string) Uuid::uuid4();

        $data = array_map(function ($entity) {
            return [
                'entityId' => $entity->entity_id,
                'value'   => 'foobar',
            ];
        }, $childEntities->all());


        $this->delete('/test/entities/'.$entity->entity_id.'/children', ['data' => $data]);

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_array($object->invalid));
        $this->assertObjectHasAttribute('entityId', $object->invalid[0]);
        $this->assertNull($object->invalid[1]);
        $this->assertObjectHasAttribute('entityId', $object->invalid[4]);
        $this->assertEquals('The selected entity id is invalid.', $object->invalid[0]->entityId[0]->message);
        $this->assertEquals($childCount, TestEntity::find($entity->entity_id)->testMany->count());
    }
}
