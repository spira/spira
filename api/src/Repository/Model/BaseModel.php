<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 13.07.15
 * Time: 19:24
 */

namespace Spira\Repository\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;
use Spira\Repository\Collection\Collection;

class BaseModel extends Model
{
    /**
     * @var Relation[]
     */
    protected static $relationsCache = [];

    /**
     * @var BaseModel[]
     */
    protected $deleteStack = [];

    protected $isDeleted = false;

    public function __set($key, $value)
    {
        if (($this->isModel($value) || ($this->isCollection($value)) || is_null($value)) && method_exists($this, $key)){
            $models = $this->__get($key);
            $this->compareRelations($key,$value);
            $models = $this->isCollection($models)?$models->all():[$models];
            $this->deleteStack = array_merge($this->deleteStack, array_filter($models));
            $this->relations[$key] = $value;
        }else{
            parent::__set($key, $value);
        }

    }


    /**
     * Save the model and all of its relationships.
     *
     * @return bool
     */
    public function push()
    {
        if (!$this->save()) {
            return false;
        }

        foreach ($this->deleteStack as $modelToDelete)
        {
            if (!$modelToDelete->delete()) {
                return false;
            }
        }

        // To sync all of the relationships to the database, we will simply spin through
        // the relationships and save each model via this "push" method, which allows
        // us to recurse into all of these nested relations for the model instance.
        foreach ($this->relations as $key => $models) {
            $models = $models instanceof Collection
                ? $models->all() : [$models];

            $relation = static::$relationsCache[$this->getRelationCacheKey($key)];

            foreach (array_filter($models) as $model) {
                $model->preserveKeys($relation);
                if (!$model->push()) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function compareRelations($method, $value)
    {
        if (is_null($value)){
            return;
        }

        $relation = static::$relationsCache[$this->getRelationCacheKey($method)];

        if ($relation instanceof HasOne || $relation instanceof BelongsTo){
            if ($this->isCollection($value)){
                throw new SetRelationException('Can not set collection, waiting for model');
            }
        }else{
            if ($this->isModel($value)){
                throw new SetRelationException('Can not set model, waiting for collection');
            }
        }
    }

    protected function isModel($value)
    {
        return $value instanceof BaseModel;
    }

    protected function isCollection($value)
    {
        return $value instanceof \Illuminate\Database\Eloquent\Collection ||
               $value instanceof Collection;
    }

    protected function preserveKeys(Relation $relation)
    {
        if ($relation instanceof HasOneOrMany){
            $fk = str_replace($this->getTable().'.','',$relation->getForeignKey());
            $this->{$fk} = $relation->getParentKey();
        }
    }

    /**
     * @param array $options
     * @return bool|null
     * @throws \Exception
     */
    public function save(array $options = [])
    {
        if ($this->isDeleted()){
            return $this->delete();
        }

        return parent::save($options);

    }

    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }


    public function markAsDeleted()
    {
        $this->isDeleted = true;
    }

    /**
     * Get a relationship value from a method.
     * Relation cache added
     *
     * @param  string  $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getRelationshipFromMethod($method)
    {
        $relations = $this->$method();

        if (!$relations instanceof Relation) {
            throw new LogicException('Relationship method must return an object of type '
                .'Illuminate\Database\Eloquent\Relations\Relation');
        }else{
            static::$relationsCache[$this->getRelationCacheKey($method)] = $relations;
        }

        return $this->relations[$method] = $relations->getResults();
    }

    protected function getRelationCacheKey($method)
    {
        return static::class.'_'.$method;
    }

}