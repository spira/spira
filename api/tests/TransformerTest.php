<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Mockery as m;

class TransformerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make('App\Services\Transformer');

        $this->transformer = $this->app->make('App\Http\Transformers\BaseTransformer');
    }

    /**
     * Testing BaseTransformer.
     */
    public function testSnakeCaseToCamelCase()
    {
        $data = m::mock('Illuminate\Contracts\Support\Arrayable');
        $data->shouldReceive('toArray')
            ->once()
            ->andReturn(['foo_bar' => 'foobar']);

        $this->assertArrayHasKey('fooBar', $this->service->item($data, new $this->transformer()));
    }

    public function testItemNestedData()
    {
        $data = m::mock('Illuminate\Contracts\Support\Arrayable');
        $data->shouldReceive('toArray')
            ->once()
            ->andReturn([
                'foo_bar'     => 'foobar',
                'nested_data' => ['foo_bar' => true, 'foo' => true, 'bar_foo' => true],
            ]);

        $data = $this->service->item($data, new $this->transformer());

        $this->assertArrayHasKey('fooBar', $data['nestedData']);
        $this->assertArrayHasKey('barFoo', $data['nestedData']);
        $this->assertArrayNotHasKey('foo_bar', $data['nestedData']);
    }

    public function testItemWithSelfKey()
    {
        $data = m::mock('Illuminate\Contracts\Support\Arrayable');
        $data->shouldReceive('toArray')
            ->once()
            ->andReturn(['self' => 'foobar']);

        $this->assertArrayHasKey('_self', $this->service->item($data, new $this->transformer()));
    }

    public function testItemWithNestedSelfKey()
    {
        $data = m::mock('Illuminate\Contracts\Support\Arrayable');
        $data->shouldReceive('toArray')
            ->once()
            ->andReturn(['self' => 'foobar', 'foo' => ['self' => 'foobar']]);

        $data = $this->service->item($data, new $this->transformer());

        $this->assertArrayHasKey('_self', $data);
        $this->assertArrayHasKey('_self', $data['foo']);
    }

    /**
     * Testing Transformer Service.
     */
    public function testCollection()
    {
        $entities = factory(App\Models\TestEntity::class, 3)->make();

        $collection = $this->service->collection($entities);

        $this->assertCount(3, $collection);
    }

    public function testItem()
    {
        $entity = factory(App\Models\TestEntity::class)->make();

        $item = $this->service->item($entity);

        $this->assertTrue(is_array($item));
    }

    public function testNonArrayableItem()
    {
        $array = ['foo' => 'bar'];

        $item = $this->service->item($array);

        $this->assertTrue(is_array($item));
    }

    public function testPaginated()
    {
        $entities = factory(App\Models\TestEntity::class, 3)->make();

        $paginated = new LengthAwarePaginator($entities, 3, 1, 1);

        $collection = $this->service->paginatedCollection($paginated);

        $this->assertTrue(is_array($collection));
    }

    public function testParseIncludes()
    {
        $entity = factory(App\Models\TestEntity::class)->make();

        $this->service->parseIncludes('foobar');

        $item = $this->service->item($entity);
    }
}
