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

        $data = $entity->toArray();
        unset($data['entity_id']);
        $data['hidden'] = true;

        $result = $this->repository->create($data);
        $this->assertTrue(is_object($result));
    }

    public function testCreateMany()
    {
        $rowCount = $this->repository->count();

        $entities = factory(App\Models\TestEntity::class, 5)->make();

        // Remove entity_ids, so we have clean new data to insert. And add hidden.
        $entities = $entities->toArray();
        $entities = array_map(function ($arr) {
            unset($arr['entity_id']);
            $arr['hidden'] = true;
            return $arr;
        }, $entities);

        $this->repository->createMany($entities);

        $this->assertEquals($rowCount + 5, $this->repository->count());
    }

    public function testCreateOrReplaceNew()
    {
        $rowCount = $this->repository->count();

        $entity = factory(App\Models\TestEntity::class)->make();
        $entity = $entity->toArray();
        $entity['hidden'] = true;
        $id = array_pull($entity, 'entity_id');

        $entity = $this->repository->createOrReplace($id, $entity);

        $this->assertEquals($rowCount + 1, $this->repository->count());
    }

    public function testCreateOrReplaceUpdate()
    {
        $entity = factory(App\Models\TestEntity::class)->create();

        $rowCount = $this->repository->count();
        $id = $entity->entity_id;

        $entityUpdate = factory(App\Models\TestEntity::class)->make();
        $entityUpdate = $entityUpdate->toArray();
        unset($entityUpdate['entity_id']);
        $entityUpdate['hidden'] = true;

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

        // Remove entity_ids, so we have clean new data to insert. And add hidden.
        $entities = $entities->toArray();
        $entities = array_map(function ($arr) {
            unset($arr['entity_id']);
            $arr['hidden'] = true;
            return $arr;
        }, $entities);

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
