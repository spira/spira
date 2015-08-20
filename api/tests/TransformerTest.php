<?php

use App\Http\Transformers\EloquentModelTransformer;
use App\Services\TransformerService;
use Mockery as m;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @property EloquentModelTransformer transformer
 */
class TransformerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->transformer = $this->app->make('App\Http\Transformers\EloquentModelTransformer');
        $this->service = $this->app->make(TransformerService::class);
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

        $transformed = $this->transformer->transformItem($data);
        $this->assertArrayHasKey('fooBar', $transformed);
        $this->assertArrayNotHasKey('_self', $transformed);
    }

    public function testItemNestedData()
    {
        $data = m::mock('Illuminate\Contracts\Support\Arrayable');
        $data->shouldReceive('toArray')
            ->once()
            ->andReturn([
                'foo_bar' => 'foobar',
                'nested_data' => ['foo_bar' => true, 'foo' => true, 'bar_foo' => true]
            ]);

        $data = $this->transformer->transformItem($data);

        $this->assertArrayHasKey('fooBar', $data['nestedData']);
        $this->assertArrayHasKey('barFoo', $data['nestedData']);
        $this->assertArrayNotHasKey('foo_bar', $data['nestedData']);
    }

    /**
     * Testing Transformer Service.
     */
    public function testCollection()
    {
        $entities = factory(App\Models\TestEntity::class, 3)->make();

        $collection = $this->transformer->transformCollection($entities);

        $this->assertCount(3, $collection);
    }

    public function testItem()
    {
        $entity = factory(App\Models\TestEntity::class)->make();

        $item = $this->transformer->transformItem($entity);

        $this->assertTrue(is_array($item));
    }

    public function testTransfomerService()
    {
        $checkArray = ['item'=>'foo'];
        $transformed = $this->transformer->getService()->item(new \Illuminate\Database\Eloquent\Collection($checkArray));
        $this->assertEquals($checkArray, $transformed);
    }

    public function testNonArrayableItem()
    {
        $array = ['foo' => 'bar'];

        $item = $this->transformer->transformItem($array);

        $this->assertTrue(is_array($item));
    }

    public function testRelationSelf()
    {
        $entity = factory(App\Models\TestEntity::class)->make();
        $hasOneEntity = factory(App\Models\SecondTestEntity::class)->make();
        $hasManyEntity = factory(App\Models\SecondTestEntity::class, 2)->make()->all();

        $entity->testOne = $hasOneEntity;
        $entity->testMany = $hasManyEntity;
        $data = $this->transformer->transformItem($entity);

        $this->assertArrayHasKey('_self', $data);
        $this->assertArrayHasKey('_testOne', $data);
        $this->assertArrayHasKey('_testMany', $data);
        $this->assertArrayHasKey('_self', $data['_testOne']);
        foreach ($data['_testMany'] as $value) {
            $this->assertArrayHasKey('_self', $value);
        }
    }

    public function testAuthTokenTransformerCollection()
    {
        $this->setExpectedExceptionRegExp(
            App\Exceptions\NotImplementedException::class,
            '/tokens.*/',
            0
        );

        $transformer = $this->app->make(App\Http\Transformers\AuthTokenTransformer::class);
        $transformer->transformCollection([]);
    }
}
