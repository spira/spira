<?php namespace App\Services;

use Illuminate\Container\Container;

class ModelFactory
{

    protected $factory;

    /**
     * Initialise the factory
     */
    public function __construct()
    {
        $this->factory = Container::getInstance()->make('Illuminate\Database\Eloquent\Factory');
    }

    /**
     * Get a model factory entity. e.g. $factory->make(\App\Models\User::class, 1, ['email'=>'joe.bloggs@example.com'])
     * @param $factoryName
     * @param int $count
     * @param array $overrides
     * @return mixed
     */
    public function make($factoryName, $count = 1, $overrides = [])
    {

        $factoryInstance = null;
        if (is_array($factoryName)){
            $factoryInstance = $this->factory->of($factoryName[0], $factoryName[1]);
        }else{
            $factoryInstance = $this->factory->of($factoryName);
        }

        return $factoryInstance->times($count)->make($overrides);
    }

    /**
     * Get a model factory entity as a json string
     * @param $factoryName
     * @param int $count
     * @param array $overrides
     * @return string
     */
    public function json($factoryName, $count = 1, $overrides = [])
    {

        $entity = $this->make($factoryName, $count, $overrides);

        return json_encode($entity->toArray(), JSON_PRETTY_PRINT);
    }

}