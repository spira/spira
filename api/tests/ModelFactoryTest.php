<?php

/**
 * Class ModelFactoryTest
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
     */
    public function testMakeModel()
    {
        $normalFactory = factory(\App\Models\User::class)->make()->toArray();

        $serviceCreatedFactory = $this->modelFactory->make(\App\Models\User::class)->toArray();

        $this->assertEquals(array_keys($normalFactory), array_keys($serviceCreatedFactory));

    }

    /**
     * Verify that the *named* factories produce the same structured objects (values will be different)
     */
    public function testMakeNamedModel()
    {
        $normalFactory = factory(App\Models\User::class, 'admin')->make()->toArray();

        $serviceCreatedFactory = $this->modelFactory->make([\App\Models\User::class, 'admin'])->toArray();

        $this->assertEquals(array_keys($normalFactory), array_keys($serviceCreatedFactory));

        $this->assertEquals($normalFactory['userType'], $serviceCreatedFactory['userType']);
    }

    /**
     * Test that valid json is returned
     */
    public function testJsonModel()
    {
        $serviceJson = $this->modelFactory->json(App\Models\User::class);

        $this->assertJson($serviceJson);

        $decoded = json_decode($serviceJson, true);

        $this->assertArrayHasKey('userType', $decoded); //assert that keys have been camel cased by transfomer
        $this->assertArrayHasKey('_self', $decoded);

    }


    /**
     * Test that call can restrict the columns returned
     */
    public function testPropertyLimitWhitelist()
    {

        $retrieveOnly = ['firstName', 'lastName'];

        $serviceJson = $this->modelFactory->json(App\Models\User::class, 1, [], $retrieveOnly);

        $decoded = json_decode($serviceJson, true);

        $this->assertEquals($retrieveOnly, array_keys($decoded));

    }

    /**
     * Test that call can restrict the columns returned by blacklist
     */
    public function testPropertyLimitBlacklist()
    {

        $dontRetrieve = ['firstName'];

        $serviceJson = $this->modelFactory->json(App\Models\User::class, 1, [], $dontRetrieve, true);

        $decoded = json_decode($serviceJson, true);

        $this->assertArrayNotHasKey($dontRetrieve[0], array_keys($decoded));

    }
}
