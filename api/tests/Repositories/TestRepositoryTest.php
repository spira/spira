<?php

use Mockery as m;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TestRepositoryTest extends TestCase
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

        // Get a repository instance
        $this->repository = $this->app->make('App\Repositories\TestRepository');
    }

    /**
     * Prepare an entity from the model factory for a testing scenario.
     *
     * @param  array $entity
     * @return array
     */
    protected function prepareEntity($entity)
    {
        // The testentity factory generates an UUID for each entity already in
        // the factory. When we test to create new entities where an UUID will
        // be generated on creation, we want this to be removed from the data.
        unset($entity['entity_id']);

        return $entity;
    }

    public function testFind()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();
        $entity = $entities->random();

        $result = $this->repository->find($entity->entity_id);
        $this->assertTrue(is_object($result));
    }

    public function testFailingFind()
    {
        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');

        $result = $this->repository->find(null);
    }

    public function testAll()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();

        $result = $this->repository->all();
        $this->assertTrue(is_array($result->toArray()));
        $this->assertGreaterThanOrEqual(5, $result->count());
    }

    public function testCreate()
    {
        $entity = factory(App\Models\TestEntity::class)->make();
        $data = $this->prepareEntity($entity->getAttributes());

        $result = $this->repository->create($data);
        $this->assertTrue(is_object($result));
    }

    public function testCreateMany()
    {
        $rowCount = $this->repository->count();

        $entities = factory(App\Models\TestEntity::class, 5)->make();
        $entities = array_map(function ($entity) {
            return $this->prepareEntity($entity->getAttributes());
        }, $entities->all());

        $this->repository->createMany($entities);
        $this->assertEquals($rowCount + 5, $this->repository->count());
    }

    public function testCreateOrReplaceNew()
    {
        $rowCount = $this->repository->count();

        $entity = factory(App\Models\TestEntity::class)->make();
        $entity = $entity->getAttributes();
        $id = array_pull($entity, 'entity_id');

        $entity = $this->repository->createOrReplace($id, $entity);
        $this->assertEquals($rowCount + 1, $this->repository->count());
    }

    public function testCreateOrReplaceUpdate()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $id = $entity->entity_id;

        $entityUpdate = factory(App\Models\TestEntity::class)->make();
        $entityUpdate = $this->prepareEntity($entityUpdate->getAttributes());

        $rowCount = $this->repository->count();

        $this->repository->createOrReplace($id, $entityUpdate);

        $updated = $this->repository->find($id);
        $this->assertEquals($rowCount, $this->repository->count());
        $this->assertEquals($updated->entity_id, $entity->entity_id);
        $this->assertNotEquals($updated->varchar, $entity->varchar);
    }

    public function testCreateOrReplaceMany()
    {
        $rowCount = $this->repository->count();

        $entities = factory(App\Models\TestEntity::class, 5)->make();
        $entities = array_map(function ($entity) {
            return $this->prepareEntity($entity->getAttributes());
        }, $entities->all());

        $this->repository->createOrReplaceMany($entities);
        $this->assertEquals($rowCount + 5, $this->repository->count());
    }

    public function testUpdate()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $id = $entity->entity_id;

        $data = ['varchar' => 'foo', 'text' => 'bar'];
        $this->repository->update($id, $data);

        $entity = $this->repository->find($id);
        $this->assertEquals('foo', $entity->varchar);
        $this->assertEquals('bar', $entity->text);
    }

    public function testUpdateMany()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();
        $ids = $entities->lists('entity_id');

        $entities = array_map(function ($id) {
            return [
                'entity_id' => $id,
                'text' => 'foobar'
            ];
        }, $ids->toArray());

        $this->repository->updateMany($entities);

        $entity = $this->repository->find($ids->random());
        $this->assertEquals('foobar', $entity->text);
    }

    public function testDelete()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $rowCount = $this->repository->count();
        $id = $entity->entity_id;

        $entity = $this->repository->find($id);
        $this->assertEquals($id, $entity->entity_id);

        $this->repository->delete($id);
        $this->assertEquals($rowCount - 1, $this->repository->count());

        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');
        $entity = $this->repository->find($id);
    }

    public function testDeleteMany()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();
        $rowCount = $this->repository->count();
        $ids = $entities->lists('entity_id')->toArray();

        $this->repository->deleteMany($ids);

        $this->assertEquals($rowCount - 5, $this->repository->count());
    }
}
