<?php namespace App\Services;

use App\Http\Transformers\BaseTransformer;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;

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
                    $transformedEntity = $blacklistProperties ? array_except($entity, $properties) : array_only($entity, $properties);
            }

            dd($properties, $transformedEntity);
        }




        return json_encode($transformedEntity, JSON_PRETTY_PRINT);
    }

}