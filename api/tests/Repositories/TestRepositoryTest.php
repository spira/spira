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
        //null to Model

        $rootEntity = factory(App\Models\TestEntity::class)->create();
        $entity = factory(App\Models\SecondTestEntity::class)->create();
        $this->assertNull($rootEntity->testOne);
        $rootEntity->testOne = $entity;
        $this->assertEquals($entity, $rootEntity->testOne);
        $this->repository->save($rootEntity);
        $compareRootEntity = $this->repository->find($rootEntity->entity_id);
        $compareEntity = $compareRootEntity->testOne;
        $this->assertEquals($entity->id, $compareEntity->id);

        //Model to Model

        $secondEntity = factory(App\Models\SecondTestEntity::class)->create();
        $rootEntity->testOne = $secondEntity;
        $this->repository->save($rootEntity);
        $this->assertFalse($entity->exists);
        $compareRootEntity = $this->repository->find($rootEntity->entity_id);
        $compareEntity = $compareRootEntity->testOne;
        $this->assertEquals($secondEntity->id, $compareEntity->id);

        //Model to null

        $rootEntity->testOne = null;
        $this->repository->save($rootEntity);
        $this->assertFalse($secondEntity->exists);
        $compareRootEntity = $this->repository->find($rootEntity->entity_id);
        $this->assertNull($compareRootEntity->testOne);
    }


    public function testPersistMany()
    {
        //empty collection to array
        $rootEntity = factory(App\Models\TestEntity::class)->create();
        $entities = factory(App\Models\SecondTestEntity::class, 5)->create()->all();
        $this->assertInstanceOf(\Spira\Repository\Collection\Collection::class, $rootEntity->testMany);
        $this->assertEquals(0, $rootEntity->testMany->count());
        $rootEntity->testMany = $entities;
        $this->assertEquals(count($entities), $rootEntity->testMany->count());
        $this->repository->save($rootEntity);
        $compareRootEntity = $this->repository->find($rootEntity->entity_id);
        $this->assertInstanceOf(\Spira\Repository\Collection\Collection::class, $compareRootEntity->testMany);
        $this->assertEquals(count($entities), $rootEntity->testMany->count());

        //add to non-empty collection
        $extraEntity = factory(App\Models\SecondTestEntity::class)->create();
        $rootEntity->testMany->add($extraEntity);
        $this->assertEquals(count($entities)+1, $rootEntity->testMany->count());
        $this->repository->save($rootEntity);
        $this->assertEquals(count($entities)+1, $rootEntity->testMany->count());
        $compareRootEntity = $this->repository->find($rootEntity->entity_id);
        $this->assertInstanceOf(\Spira\Repository\Collection\Collection::class, $compareRootEntity->testMany);
        $this->assertEquals(count($entities)+1, count($compareRootEntity->testMany->all()));

        //add to empty collection
        $rootEntity2 = factory(App\Models\TestEntity::class)->create();
        $this->assertEquals($rootEntity2->testMany->count(), 0);
        $rootEntity2->testMany->add($extraEntity);
        $this->repository->save($rootEntity2);
        $this->assertEquals($rootEntity2->testMany->count(), 1);
        $this->assertEquals($extraEntity, $rootEntity2->testMany->first());
        $compareRootEntity = $this->repository->find($rootEntity2->entity_id);
        $this->assertInstanceOf(\Spira\Repository\Collection\Collection::class, $compareRootEntity->testMany);
        $this->assertEquals($extraEntity->entity_id, $rootEntity2->testMany->first()->entity_id);

        //add same entity to collection
        $rootEntity3 = factory(App\Models\TestEntity::class)->create();
        $extraEntity2 = factory(App\Models\SecondTestEntity::class)->create();
        $rootEntity3->testMany->add($extraEntity2);
        $rootEntity3->testMany->add($extraEntity2);
        $this->assertEquals($rootEntity3->testMany->count(), 1);
        $this->repository->save($rootEntity3);
        $this->assertTrue($extraEntity2->exists);
        $rootEntity3->testMany->add($extraEntity2);
        $this->assertEquals($rootEntity3->testMany->count(), 1);
    }

    public function testReplaceHasManyCollection()
    {
        $rootEntity = factory(App\Models\TestEntity::class)->create();
        $entities = factory(App\Models\SecondTestEntity::class, 3)->create()->all();
        $entities2 = factory(App\Models\SecondTestEntity::class, 5)->create()->all();
        $rootEntity->testMany = $entities;
        $this->repository->save($rootEntity);
        $rootEntity->testMany = $entities2;
        $this->repository->save($rootEntity);
        $compareRootEntity = $this->repository->find($rootEntity->entity_id);
        $this->assertEquals(count($entities2), $compareRootEntity->testMany->count());
    }

    public function testRemoveFromMany()
    {
        //remove while not saved
        $rootEntity = factory(App\Models\TestEntity::class)->create();
        $extraEntity = factory(App\Models\SecondTestEntity::class)->create();
        $this->assertEquals(0, $rootEntity->testMany->count());
        $rootEntity->testMany->add($extraEntity);
        $this->assertEquals(1, $rootEntity->testMany->count());
        $rootEntity->testMany->remove($extraEntity);
        $this->assertEquals(0, $rootEntity->testMany->count());
        $this->assertEquals(1, $rootEntity->testMany->count(true));
        $this->repository->save($rootEntity);
        $compareRootEntity = $this->repository->find($rootEntity->entity_id);
        $this->assertEquals(0, $compareRootEntity->testMany->count(true));

        //remove saved entity
        $rootEntity2 = factory(App\Models\TestEntity::class)->create();
        $extraEntity2 = factory(App\Models\SecondTestEntity::class)->create();
        $rootEntity2->testMany->add($extraEntity2);
        $this->repository->save($rootEntity2);
        $savedRootEntity2 = $this->repository->find($rootEntity2->entity_id);
        $savedRootEntity2->testMany->remove($extraEntity2);
        $this->assertEquals(0, $savedRootEntity2->testMany->count());
        $this->assertEquals(1, $savedRootEntity2->testMany->count(true));
        $this->repository->save($savedRootEntity2);
        $compareRootEntity2 = $this->repository->find($savedRootEntity2->entity_id);
        $this->assertEquals(0, $compareRootEntity2->testMany->count());

        //remove saved entity from the same object
        $rootEntity3 = factory(App\Models\TestEntity::class)->create();
        $extraEntity3 = factory(App\Models\SecondTestEntity::class)->create();
        $rootEntity3->testMany->add($extraEntity3); //no id, object_hash is the key
        $this->repository->save($rootEntity3);
        $rootEntity3->testMany->remove($extraEntity3);
        $this->assertEquals(0, $rootEntity3->testMany->count());
        $this->assertEquals(1, $rootEntity3->testMany->count(true));
    }

    public function testAddBadModelToCollection()
    {
        $this->setExpectedException(Spira\Repository\Collection\ItemTypeException::class);
        $rootEntity = factory(App\Models\TestEntity::class)->create();
        $notValidEntity = new \StdClass();
        $rootEntity->testMany->add($notValidEntity);
    }
}
