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

        $connection = m::mock('Illuminate\Database\Connection');

        $this->assertTrue($connection instanceof Illuminate\Database\Connection);

        $connectionResolver = m::mock('Illuminate\Database\ConnectionResolverInterface')
            ->shouldReceive('connection')
            ->times()
            ->andReturn($connection)
            ->getMock();

        $this->assertTrue($connectionResolver instanceof Illuminate\Database\ConnectionResolverInterface);



        $this->baseModel = m::mock('App\Models\BaseModel')->makePartial();
        $this->app->instance('App\Models\BaseModel', $this->baseModel);

        $this->baseRepository->shouldReceive('model')
            ->once()
            ->andReturn($this->baseModel);

        $this->baseRepository->__construct($connectionResolver);
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


    public function getModel()
    {
        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');
    }

    public function testSave()
    {

    }

    public function testDelete()
    {

    }

}
