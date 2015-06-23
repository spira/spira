<?php

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

        $this->assertArrayHasKey('fooBar', $this->service->item($data, new $this->transformer));
    }
}
