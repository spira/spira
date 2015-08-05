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
use Spira\Repository\Validation\ValidationException;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Factory as ValidationFactory;
use Spira\Repository\Validation\Validator;

/**
 * Class BaseModel
 * @package Spira\Repository\Model
 *
 * @method static int count
 * @method static Collection get
 * @method static BaseModel findOrFail
 * @method static BaseModel find
 * @method static Collection findMany
 * @method static BaseModel where
 * @method static BaseModel skip offset
 * @method static BaseModel take limit
 *
 */
abstract class BaseModel extends Model
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

    public $exceptionOnError = true;

    /**
     * @var ValidationFactory
     */
    protected $validator;

    /**
     * @var MessageBag|null
     */
    protected $errors;

    protected $validationRules = [];

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return $this->validationRules;
    }

    /**
     * @return ValidationFactory
     */
    abstract protected function getValidator();

    /**
     * @return MessageBag|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws SetRelationException
     */
    public function setAttribute($key, $value)
    {
        if (method_exists($this, $key)) {
            $value = $this->prepareValue($value);

            if ($value !== false) {
                $models = $this->getRelationValue($key);
                $this->addPreviousValueToDeleteStack($models);
                $this->isValueCompatibleWithRelation($key, $value);
                $this->relations[$key] = $value;
            }
        } else {
            parent::setAttribute($key, $value);
        }
    }

    

    /**
     * Prepare value for proper assignment
     * @param array|Collection|false|BaseModel $value
     * Can be array, empty array, null, false, Collection or Model
     * @return null|Collection|BaseModel|false  false on bad value
     */
    protected function prepareValue($value)
    {
        if (empty($value)) {
            return null;
        }

        if ($this->isModel($value) || $this->isCollection($value)) {
            return $value;
        }

        if (is_array($value)) {
            $firstModel = current($value);
            if ($firstModel instanceof BaseModel) {
                return $firstModel->newCollection($value);
            }
            return false;
        }

        return false;
    }

    /**
     * @param $models
     */
    protected function addPreviousValueToDeleteStack($models)
    {
        /** @var Collection|BaseModel[] $models */
        $models = $this->isCollection($models)?$models->all(true):[$models];
        $deleteArray = [];
        foreach ($models as $model) {
            if ($model && $model->exists) {
                $deleteArray[] = $model;
            }
        }

        $this->deleteStack = array_merge($this->deleteStack, $deleteArray);
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

        foreach ($this->deleteStack as $modelToDelete) {
            if (!$modelToDelete->delete()) {
                return false;
            }
            $this->deleteStack = [];
        }

        // To sync all of the relationships to the database, we will simply spin through
        // the relationships and save each model via this "push" method, which allows
        // us to recurse into all of these nested relations for the model instance.
        foreach ($this->relations as $key => $models) {
            /** @var Collection|array $models */
            $models = $this->isCollection($models)? $models->all(true) : [$models];
            $relation = static::$relationsCache[$this->getRelationCacheKey($key)];
            foreach (array_filter($models) as $model) {
                /** @var BaseModel $model */
                $model->preserveKeys($relation);
                if (!$model->push()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks relation against value
     * @param $method
     * @param $value
     * @return bool
     * @throws SetRelationException
     */
    protected function isValueCompatibleWithRelation($method, $value)
    {
        if (is_null($value)) {
            return true;
        }

        $relation = static::$relationsCache[$this->getRelationCacheKey($method)];

        if ($relation instanceof HasOne || $relation instanceof BelongsTo) {
            if ($this->isCollection($value)) {
                throw new SetRelationException('Can not set collection, model expected');
            }
        } else {
            if ($this->isModel($value)) {
                throw new SetRelationException('Can not set model, collection expected');
            }
        }

        return true;
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isModel($value)
    {
        return $value instanceof BaseModel;
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isCollection($value)
    {
        return $value instanceof Collection;
    }

    /**
     * @param Relation $relation
     */
    protected function preserveKeys(Relation $relation)
    {
        if ($relation instanceof HasOneOrMany) {
            $fk = str_replace($this->getTable().'.', '', $relation->getForeignKey());
            $this->attributes[$fk] = $relation->getParentKey();
        }
    }

    /**
     * @param array $options
     * @return bool|null
     * @throws \Exception
     */
    public function save(array $options = [])
    {
        if ($this->isDeleted()) {
            return $this->delete();
        }

        $result = true;
        if ($this->fireModelEvent('validating') !== false) {
            $result = $this->validate();
        }

        if (!$result && $this->exceptionOnError) {
            throw new ValidationException($this->getErrors());
        }

        if ($this->fireModelEvent('validated') === false) {
            return false;
        }


        return parent::save($options);
    }

    public function validate()
    {
        /** @var Validator $validation */
        $validation = $this->getValidator()->make($this->attributes, $this->getValidationRules());
        if (!$validation instanceof Validator){
            throw new \InvalidArgumentException('Validator must be instance of '.Validator::class);
        }
        $validation->setModel($this);
        $this->errors = [];
        if ($validation->fails()) {
            $this->errors = $validation->messages();
            return false;
        }

        return true;
    }

    /**
     * Register a validating model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @param  int  $priority
     * @return void
     */
    public static function validating($callback, $priority = 0)
    {
        static::registerModelEvent('validating', $callback, $priority);
    }

    /**
     * Register a validated model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @param  int  $priority
     * @return void
     */
    public static function validated($callback, $priority = 0)
    {
        static::registerModelEvent('validated', $callback, $priority);
    }


    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models, static::class);
    }

    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }

    /**
     *
     */
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
        } else {
            static::$relationsCache[$this->getRelationCacheKey($method)] = $relations;
        }

        return $this->relations[$method] = $relations->getResults();
    }

    /**
     * Set the specific relationship in the model.
     *
     * @param  string $relationName
     * @param  mixed $value
     * @param Relation|null $relation
     * @return $this
     */
    public function setRelation($relationName, $value, $relation = null)
    {
        if ($relation instanceof Relation){
            static::$relationsCache[$this->getRelationCacheKey($relationName)] = $relation;
        }

        return parent::setRelation($relationName, $value);
    }

    /**
     * @param $method
     * @return string
     */
    protected function getRelationCacheKey($method)
    {
        return spl_object_hash($this).'_'.$method;
    }
}
