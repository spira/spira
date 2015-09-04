<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;

class ModelFactory
{
    protected $transformerService;
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * Initialise the factory.
     */
    public function __construct()
    {
        $this->factory = Container::getInstance()->make('Illuminate\Database\Eloquent\Factory');
        $this->transformerService = App::make(TransformerService::class);
    }

    /**
     * Get a factory instance.
     *
     * @param $factoryClass
     * @param $definedName
     *
     * @return ModelFactoryInstance
     */
    public function get($factoryClass = null, $definedName = 'default')
    {
        if (is_string($factoryClass)) {
            $instance = $this->factory->of($factoryClass, $definedName);
        } else {
            $instance = null;
        }

        return new ModelFactoryInstance($instance, $this->transformerService);
    }

    /**
     * Shorthand get a json string of the entity.
     *
     * @param $factoryClass
     * @param string $definedName
     *
     * @return ModelFactoryInstance
     */
    public function json($factoryClass, $definedName = 'default')
    {
        return $this->get($factoryClass, $definedName)->json();
    }

    /**
     * Shorthand get the eloquent entity.
     *
     * @param $factoryClass
     * @param string $definedName
     *
     * @return mixed
     */
    public function make($factoryClass, $definedName = 'default')
    {
        return $this->get($factoryClass, $definedName)->modified();
    }
}
