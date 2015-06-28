<?php

use Mockery as m;
use Illuminate\Pagination\LengthAwarePaginator;

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

        $this->assertArrayHasKey('fooBar', $this->service->item($data, new $this->transformer));
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
