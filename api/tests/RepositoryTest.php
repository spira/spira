<?php

use Mockery as m;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RepositoryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        $this->mock = m::mock('App\Models\TestEntity')->makePartial();
        $this->app->instance('App\Models\TestEntity', $this->mock);

        $this->repository = $this->app->make('App\Repositories\TestRepository');
    }

    /**
     * Test TestEntity Repository.
     */
    public function testAll()
    {
        $this->mock->shouldReceive('get')
            ->once()
            ->with(m::type('array'))
            ->andReturn([]);

        $result = $this->repository->all();

        $this->assertTrue(is_array($result));
    }

    public function testCreate()
    {
        $this->mock->shouldReceive('create')
            ->once()
            ->with(m::type('array'))
            ->andReturn($this->mock);

        $result = $this->repository->create(['foo' => 'bar']);

        $this->assertTrue(is_object($result));
    }

    public function testFind()
    {
        $this->mock->shouldReceive('findOrFail')
            ->once()
            ->andReturn($this->mock);

        $result = $this->repository->find('foobar');

        $this->assertTrue(is_object($result));
    }

    public function testFailingFind()
    {
        $this->app->instance('App\Models\TestEntity', null);
        $repository = new App\Repositories\TestRepository($this->app);

        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');

        $result = $repository->find(null);
    }
}
