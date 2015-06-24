<?php

use Mockery as m;

class BaseRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->baseRepository = m::mock('App\Repositories\BaseRepository')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->baseModel = m::mock('App\Models\BaseModel')->makePartial();
        $this->app->instance('App\Models\BaseModel', $this->baseModel);

        $this->baseRepository->shouldReceive('model')
            ->once()
            ->andReturn('App\Models\BaseModel');

        $this->baseRepository->__construct($this->app);
    }

    public function testFind()
    {
        $this->baseModel->shouldReceive('findOrFail')
            ->once()
            ->andReturn($this->baseModel);

        $result = $this->baseRepository->find('foobar');

        $this->assertTrue(is_object($result));
    }

    public function testAll()
    {
        $this->baseModel->shouldReceive('get')
            ->once()
            ->with(m::type('array'))
            ->andReturn([]);

        $result = $this->baseRepository->all();

        $this->assertTrue(is_array($result));
    }

    public function testCreate()
    {
        $this->baseModel->shouldReceive('create')
            ->once()
            ->with(m::type('array'))
            ->andReturn($this->baseModel);

        $result = $this->baseRepository->create(['foo' => 'bar']);

        $this->assertTrue(is_object($result));
    }
}
