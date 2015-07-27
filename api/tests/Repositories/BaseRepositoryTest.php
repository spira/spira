<?php

use Mockery as m;

class BaseRepositoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->baseRepository = m::mock('Spira\Repository\Repository\BaseRepository')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->connection = m::mock('Illuminate\Database\Connection');

        $this->assertTrue($this->connection instanceof Illuminate\Database\Connection);

        $this->connectionResolver = m::mock('Illuminate\Database\ConnectionResolverInterface')
            ->shouldReceive('connection')
            ->times()
            ->andReturn($this->connection)
            ->getMock();

        $this->assertTrue($this->connectionResolver instanceof Illuminate\Database\ConnectionResolverInterface);



        $this->baseModel = m::mock('App\Models\BaseModel')->makePartial();
        $this->app->instance('App\Models\BaseModel', $this->baseModel);

        $this->baseRepository->shouldReceive('model')
            ->once()
            ->andReturn($this->baseModel);

        $this->baseRepository->__construct($this->connectionResolver);
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
        $this->connection->shouldReceive('beginTransaction')->once();
        $this->connection->shouldReceive('commit')->once();

        $this->baseModel->shouldReceive('push')
            ->once()
            ->andReturn(true);

        $result = $this->baseRepository->save($this->baseModel);
        $this->assertEquals($result,$this->baseModel);
    }

    public function testDelete()
    {
        $this->baseModel->shouldReceive('delete')
            ->once()
            ->andReturn(true);
        $this->baseRepository->delete($this->baseModel);
    }
}
