<?php namespace App\Services;

use App\Http\Transformers\BaseTransformer;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @return $this
     */
    public function get($factoryClass, $definedName = false)
    {

        $factoryInstance = null;
        if ($definedName){
            $factoryInstance = $this->factory->of($factoryClass, $definedName);
        }else{
            $factoryInstance = $this->factory->of($factoryClass);
        }

        return new ModelFactoryInstance($factoryInstance, $this->transformerService);
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
     * @param array $properties white/blacklist of properties e.g. ['email'] will only show the email, ['password'] will hide the password if $blacklistProperties is true
     * @param bool $blacklistProperties
     * @return string
     */
    public function json($factoryName, $count = 1, $overrides = [], $properties = [], $blacklistProperties = false)
    {

        $entity = $this->make($factoryName, $count, $overrides, $properties);

        $transformerService = App::make('App\Services\Transformer');

        $entityType = ($count > 1) ? 'collection' : 'item';

        $transformedEntity = $transformerService->{$entityType}($entity, new BaseTransformer);

        if (!empty($properties)){
            switch($entityType){
                case 'collection':
                {
                    $transformedEntity = array_map(function($entity) use ($properties, $blacklistProperties){
                        return $blacklistProperties ? array_except($entity, $properties) : array_only($entity, $properties);
                    }, $transformedEntity);
                }
                    break;

                case 'item':
                default:
                    $transformedEntity = $blacklistProperties ? array_except($transformedEntity, $properties) : array_only($transformedEntity, $properties);
            }

        }

        $jsonEncoded = json_encode($transformedEntity, JSON_PRETTY_PRINT);

        return str_replace("\n", "\n            ", $jsonEncoded); //cheap trick to make sure the 12 deep indentation requirement of apiary is preserved
    }

}

class ModelFactoryInstance
{

    private $transformerService;
    private $factoryInstance;
    private $customizations = [];
    private $entityCount = 1;
    private $transformer;
    private $appends;
    private $builtEntity;
    private $makeVisible;
    private $showOnly;


    /**
     * New model instance
     * @param $factoryInstance
     * @param $transformerService
     */
    public function __construct($factoryInstance, $transformerService)
    {
        $this->factoryInstance = $factoryInstance;
        $this->transformerService = $transformerService;

        return $this;
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

    public function append($appends)
    {
        $this->appends = $appends;
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

    public function transform($transformerName)
    {
        $this->transformer = new $transformerName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function build()
    {

        $entity = $this->factoryInstance
            ->times($this->entityCount)
            ->make($this->customizations)
        ;

        $entityType = ($this->entityCount > 1) ? 'collection' : 'item';

        switch($entityType){
            case 'item':
                $entity = $this->modifyEntity($entity);
                break;
            case 'collection':
                $entity = $entity->each(function($singleEntity){
                    return $this->modifyEntity($singleEntity);
                });
                break;
        }

        if (!$this->transformer){
            $this->transformer = new BaseTransformer();
        }

        $transformedEntity = $this->transformerService->{$entityType}($entity, $this->transformer);

        $this->builtEntity = $transformedEntity;

        return $this->builtEntity;
    }


    public function json()
    {

        if (!$this->builtEntity){
            $this->build();
        }

        $entity = $this->builtEntity;

        $jsonEncoded = json_encode($entity, JSON_PRETTY_PRINT);

        return str_replace("\n", "\n            ", $jsonEncoded); //cheap trick to make sure the 12 deep indentation requirement of apiary is preserved

    }

    /**
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
}