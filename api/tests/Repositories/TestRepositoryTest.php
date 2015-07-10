<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Rhumsaa\Uuid\Uuid;

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
        $data = $entity->getAttributes();

        $result = $this->repository->create($data);
        $this->assertTrue(is_array($result));
    }

    public function testCreateMany()
    {
        $rowCount = $this->repository->count();

        $entities = factory(App\Models\TestEntity::class, 5)->make();
        $entities = array_map(function ($entity) {
            return $entity->getAttributes();
        }, $entities->all());

        $this->repository->createMany($entities);
        $this->assertEquals($rowCount + 5, $this->repository->count());
    }

    public function testCreateOrReplaceNew()
    {
        $rowCount = $this->repository->count();

        $entity = factory(App\Models\TestEntity::class)->make();
        $id = $entity->entity_id;
        $entity = $entity->getAttributes();

        $entity = $this->repository->createOrReplace($id, $entity);
        $this->assertEquals($rowCount + 1, $this->repository->count());
    }

    public function testCreateOrReplaceUpdate()
    {
        $entity = factory(App\Models\TestEntity::class)->create([
            'varchar' => 'foobar',
        ]);
        $id = $entity->entity_id;

        $entityUpdate = factory(App\Models\TestEntity::class)->make([
            'entity_id' => $id, //make sure the id doesn't change
            'varchar'   => 'foobaz',
        ]);
        $entityUpdate = $entityUpdate->getAttributes();

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
            return array_add($entity->getAttributes(), 'entity_id', (string) Uuid::uuid4());
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
                'text'      => 'foobar',
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
