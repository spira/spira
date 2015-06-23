<?php

use Mockery as m;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TestRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make('App\Repositories\TestRepository');

        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
        App\Models\TestEntity::flushEventListeners();
        App\Models\TestEntity::boot();
    }


    public function testFailingFind()
    {
        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');

        $result = $this->repository->find(null);
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
}
