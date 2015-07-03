<?php

/**
 * Class ModelFactoryTestDrop
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

        $serviceCreatedFactory = $this->modelFactory->get(\App\Models\User::class)->toArray();

        $this->assertEquals(array_keys($normalFactory), array_keys($serviceCreatedFactory));

    }

    /**
     * Verify that the *named* factories produce the same structured objects (values will be different)
     */
    public function testMakeNamedModel()
    {
        $normalFactory = factory(App\Models\User::class, 'admin')->make()->toArray();

        $serviceCreatedFactory = $this->modelFactory->get(\App\Models\User::class, 'admin')->toArray();

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
     * Test that call can restrict the columns returned for a single entity
     */
    public function testPropertyLimitWhitelistEntity()
    {

        $retrieveOnly = ['first_name', 'last_name'];

        $serviceModel = $this->modelFactory->get(App\Models\User::class)
            ->showOnly($retrieveOnly)
            ->toArray()
        ;

        $this->assertEquals($retrieveOnly, array_keys($serviceModel));

    }

    /**
     * Test that call can restrict the columns returned for a group of entities
     */
    public function testPropertyLimitWhitelistCollection()
    {

        $retrieveOnly = ['first_name', 'last_name'];

        $serviceModel = $this->modelFactory->get(App\Models\User::class)
            ->count(2)
            ->showOnly($retrieveOnly)
            ->toArray()
        ;

        $this->assertEquals($retrieveOnly, array_keys($serviceModel[0]));

    }

    /**
     * Test that call can make a normally hidden column visible
     */
    public function testHiddenPropertyShowing()
    {

        $showProperty = 'password';

        $user = new \App\Models\User();
        $this->assertContains($showProperty, $user->getHidden());

        $serviceModel = $this->modelFactory->get(App\Models\User::class)
            ->makeVisible([$showProperty])
            ->toArray()
        ;

        $this->assertArrayHasKey($showProperty, $serviceModel);

    }

    /**
     * Test that the $factory->make() method returns eloquent models
     */
    public function testFactoryMakesEloquent(){

        $entity = $this->modelFactory->make(App\Models\User::class);

        $this->assertInstanceOf(Illuminate\Database\Eloquent\Model::class, $entity);

    }

    public function testModelFactoryFullChain()
    {
        $fixture = [
            'firstName' => 'zak',
            'password' => 'mypass',
            'userType' => 'admin',
        ];

        $collection = [$fixture, $fixture];

        $serviceCreatedFactoryJson = $this->modelFactory->get(\App\Models\User::class, 'admin')
            ->customize(['first_name'=>'zak', 'password' => 'mypass'])
            ->makeVisible(['password'])
            ->showOnly(['password', 'first_name', 'userType'])
            ->count(2)
            ->setTransformer(App\Http\Transformers\BaseTransformer::class)
            ->json();

        $this->assertJson($serviceCreatedFactoryJson);
        $this->assertEquals($collection, json_decode($serviceCreatedFactoryJson, true));
    }

    public function testModelFactoryInstanceArrayableAndJsonable()
    {
        $serviceCreatedFactoryInstance = $this->modelFactory->get(\App\Models\User::class);

        $this->assertInstanceOf(Illuminate\Contracts\Support\Arrayable::class, $serviceCreatedFactoryInstance);
        $this->assertInstanceOf(Illuminate\Contracts\Support\Jsonable::class, $serviceCreatedFactoryInstance);
        $this->assertJson($serviceCreatedFactoryInstance->toJson());
    }

}
