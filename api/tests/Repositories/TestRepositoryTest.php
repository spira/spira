<?php

use Mockery as m;
use Rhumsaa\Uuid\Uuid;
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

        App\Models\SecondTestEntity::flushEventListeners();
        App\Models\SecondTestEntity::boot();

        // Get a repository instance
        $this->repository = $this->app->make('App\Repositories\TestRepository');
        $this->secondRepository = $this->app->make('App\Repositories\SecondTestRepository');
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

    public function testSave()
    {
        $entity = factory(App\Models\TestEntity::class)->make();
        $this->assertFalse($entity->exists);

        $result = $this->repository->save($entity);
        $this->assertInstanceOf(App\Models\TestEntity::class, $result);
        $this->assertTrue($result->exists);

    }

    public function testSaveMany()
    {
        $rowCount = $this->repository->count();
        $entities = factory(App\Models\TestEntity::class, 5)->make();
        $this->repository->saveMany($entities);
        $this->assertEquals($rowCount + 5, $this->repository->count());
    }


    public function testUpdate()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $id = $entity->entity_id;

        $data = ['varchar' => 'foo', 'text' => 'bar'];
        $entity->fill($data);
        $this->repository->save($entity);

        $entity = $this->repository->find($id);
        $this->assertEquals('foo', $entity->varchar);
        $this->assertEquals('bar', $entity->text);
    }

    public function testUpdateMany()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();


        $this->repository->saveMany($entities);

        foreach ($entities as $entity) {
            $this->assertNotEquals('foobar', $entity->text);
            $this->assertTrue($entity->exists);
            $entity->text = 'foobar';
        }

        $this->repository->saveMany($entities);

        foreach ($entities as $entity) {
            $compareEntity = $this->repository->find($entity->entity_id);
            $this->assertEquals('foobar', $compareEntity->text);
        }


    }

    public function testDelete()
    {
        $entity = factory(App\Models\TestEntity::class)->create();
        $rowCount = $this->repository->count();
        $id = $entity->entity_id;

        $entity = $this->repository->find($id);

        $this->repository->delete($entity);
        $this->assertEquals($rowCount - 1, $this->repository->count());

        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');
        $entity = $this->repository->find($id);
    }

    public function testDeleteMany()
    {
        $entities = factory(App\Models\TestEntity::class, 5)->create();
        $rowCount = $this->repository->count();

        $this->repository->deleteMany($entities);

        $this->assertEquals($rowCount - 5, $this->repository->count());
    }

    public function testPersistOne()
    {
        $rootEntity = factory(App\Models\TestEntity::class)->create();
        $entity = factory(App\Models\SecondTestEntity::class)->create();

        $rootEntity->testOne = $entity;
        $this->assertEquals($entity, $rootEntity->testOne);

        $this->repository->save($rootEntity);

        $compareRootEntity = $this->repository->find($rootEntity->entity_id);
        $compareEntity = $compareRootEntity->testOne;

        $this->assertEquals($entity->id, $compareEntity->id);
    }


    public function testPersistRemoveOne()
    {
        $rootEntity = factory(App\Models\TestEntity::class)->create();
        $entity = factory(App\Models\SecondTestEntity::class)->create();

        $rootEntity->testOne = $entity;
        $this->assertEquals($entity, $rootEntity->testOne);

        $this->repository->save($rootEntity);

        $rootEntity->testOne = null;

        $this->repository->save($rootEntity);

        $compareRootEntity = $this->repository->find($rootEntity->entity_id);
        $compareEntity = $compareRootEntity->testOne;

        $this->assertNull($compareEntity);

    }

    public function testPersistMany()
    {
        $rootEntity = factory(App\Models\SecondTestEntity::class)->create();
        $entities = factory(App\Models\TestEntity::class, 5)->create();

        $entities = new \Spira\Repository\Collection\Collection($entities);

        $rootEntity->testMany = $entities;
        $this->assertEquals($entities, $rootEntity->testMany);

        $this->secondRepository->save($rootEntity);

        $extraEntity = factory(App\Models\TestEntity::class, 5)->create();
    }
}


