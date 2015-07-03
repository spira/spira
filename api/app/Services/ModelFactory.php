<?php namespace App\Services;

use App\Http\Transformers\BaseTransformer;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\App;

class ModelFactory
{

    protected $transformerService;
    protected $factory;

    /**
     * Initialise the factory
     */
    public function __construct()
    {
        $this->factory = Container::getInstance()->make('Illuminate\Database\Eloquent\Factory');
        $this->transformerService = App::make('App\Services\Transformer');
    }

    /**
     * Get a factory instance
     * @param $factoryClass
     * @param $definedName
     * @return ModelFactoryInstance
     */
    public function get($factoryClass, $definedName = false)
    {
        $instance = $this->getFactoryInstance($factoryClass, $definedName);
        return new ModelFactoryInstance($instance, $this->transformerService);
    }

    /**
     * @param $factoryClass
     * @param bool $definedName
     * @return ModelFactoryInstance
     */
    public function json($factoryClass, $definedName = false)
    {
        return $this->get($factoryClass, $definedName)->json();
    }

    private function getFactoryInstance($factoryClass, $definedName = false)
    {
        if ($definedName){
            return $this->factory->of($factoryClass, $definedName);
        }else{
            return $this->factory->of($factoryClass);
        }
    }

}

class ModelFactoryInstance implements Arrayable, Jsonable
{

    private $transformerService;
    private $factoryInstance;
    private $customizations = [];
    private $entityCount = 1;
    private $transformer;
    private $makeVisible;
    private $showOnly;
    private $entityType;


    /**
     * New model instance
     * @param $factoryInstance
     * @param $transformerService
     * @param bool $json
     */
    public function __construct($factoryInstance, $transformerService)
    {
        $this->factoryInstance = $factoryInstance;
        $this->transformerService = $transformerService;
    }


    public function count($number)
    {
        $this->entityCount = $number;
        return $this;
    }

    public function customize($customizations)
    {
        $this->customizations = $customizations;
        return $this;
    }

    public function makeVisible($makeVisible)
    {
        $this->makeVisible = $makeVisible;
        return $this;
    }

    public function showOnly($showOnly)
    {
        $this->showOnly = $showOnly;
        return $this;
    }

    public function setTransformer($transformerName)
    {
        $this->transformer = new $transformerName;
        return $this;
    }

    /**
     * Get the built entities
     * @return mixed
     */
    private function built()
    {
        $entity = $this->factoryInstance
            ->times($this->entityCount)
            ->make($this->customizations)
        ;

        $this->entityType = ($this->entityCount > 1) ? 'collection' : 'item';

        return $entity;
    }

    /**
     * Modify an entity
     * @param $entity
     */
    private function modifyEntity($entity)
    {
        if ($this->showOnly) {
            $attributes = $entity->getAttributes();
            $appends = $entity->appends;
            $newHidden = array_diff(array_merge(array_keys($attributes), $appends), $this->showOnly);
            $entity->setHidden($newHidden);
        }

        if ($this->makeVisible) {
            $hidden = $entity->getHidden();

            $newHidden = array_diff($hidden, $this->makeVisible);

            $entity->setHidden($newHidden);
        }

        return $entity;
    }

    /**
     * Get the modified entity[ies]
     * @return mixed
     */
    public function modified()
    {
        $entity = $this->built();
        switch ($this->entityType) {
            case 'item':
                $entity = $this->modifyEntity($entity);
                break;
            case 'collection':
                $entity = $entity->each(
                    function ($singleEntity) {
                        return $this->modifyEntity($singleEntity);
                    }
                );
                break;
        }
        return $entity;
    }

    public function transformed(){
        $entity = $this->built();

        if (!$this->transformer) {
            $this->transformer = new BaseTransformer();
        }

        $transformedEntity = $this->transformerService->{$this->entityType}($entity, $this->transformer);
        return $transformedEntity;
    }


    /**
     * Get the built & modified entity[ies]
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->modified()->toArray();
    }

    /**
     * Get the JSON encoded string of the (built, modified, transformed) entity[ies]
     * @param int $options
     * @return string
     */
    public function json($options = JSON_PRETTY_PRINT)
    {

        $transformed = $this->transformed();

        $jsonEncoded = json_encode($transformed, $options);

        return str_replace("\n", "\n            ", $jsonEncoded); //cheap trick to make sure the 12 deep indentation requirement of apiary is preserved

    }

    /**
     * {@inheritdoc}
     */
    public function toJson($options = 0)
    {
        return $this->json($options);
    }


}