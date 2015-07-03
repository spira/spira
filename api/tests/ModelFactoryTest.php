<?php

/**
 * Class ModelFactoryTest
 * @group testing
 */
class ModelFactoryTest extends TestCase
{

    private $modelFactory;

    public function setUp()
    {
        parent::setUp();

        $this->modelFactory = $this->app->make('App\Services\ModelFactory');
    }

    /**
     * Verify that the factories produce the same structured objects (values will be different)
     * @group failing
     */
    public function testMakeModel()
    {
        $normalFactory = factory(\App\Models\User::class)->make()->toArray();

        $serviceCreatedFactory = $this->modelFactory->make(\App\Models\User::class)->toArray();

        $this->assertEquals(array_keys($normalFactory), array_keys($serviceCreatedFactory));

    }

    /**
     * Verify that the factories produce the same structured objects (values will be different)
     * @group failing
     */
    public function testMakeNamedModel()
    {
        $normalFactory = factory(\App\Models\User::class, '')->make()->toArray();

        $serviceCreatedFactory = $this->modelFactory->make(\App\Models\User::class)->toArray();

        $this->assertEquals(array_keys($normalFactory), array_keys($serviceCreatedFactory));

    }

}
