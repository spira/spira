<?php

use App\Services\ModelFactory;

trait ModelFactoryTrait
{
    /**
     * Making it static not to reinit for each TestCase
     * @var ModelFactory
     */
    protected static $modelFactory;

    public function bootModelFactoryTrait()
    {
        if (is_null(static::$modelFactory)){
            static::$modelFactory = \App::make(ModelFactory::class);
        }
    }

    /**
     * @return ModelFactory
     */
    public function getFactory()
    {
        return static::$modelFactory;
    }
}