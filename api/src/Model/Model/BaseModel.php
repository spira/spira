<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 13.07.15
 * Time: 19:24
 */

namespace Spira\Model\Model;

use Bosnadev\Database\Traits\UuidTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;
use Spira\Model\Collection\Collection;
use Spira\Model\Validation\ValidationException;
use Illuminate\Support\MessageBag;
use Spira\Model\Validation\ValidationExceptionCollection;
use \Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Class BaseModel
 * @package Spira\Model\Model
 *
 * @method static int count
 * @method static BaseModel find($id)
 * @method static BaseModel first()
 * @method static BaseModel findOrFail($id)
 * @method static Collection get
 * @method static Collection findMany($ids)
 * @method static Builder where($value,$operator,$operand)
 * @method static BaseModel skip($offset)
 * @method static BaseModel take($limit)
 *
 */
abstract class BaseModel extends Model
{

    use UuidTrait;
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

    public $incrementing = false;


    /**
     * @var MessageBag|null
     */
    protected $errors;

    /**
     * @var MessageBag[]
     */
    protected $relationErrors = [];

    protected static $validationRules = [];

    /**
     * @return array
     */
    public static function getValidationRules()
    {
        return static::$validationRules;
    }


    /**
     * @return mixed
     */
    public static function getTableName()
    {
        return with(new static())->getTable();
    }

    /**
     * @return MessageBag|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $relationName
     * @return \Exception|null
     */
    public function getRelationErrors($relationName)
    {
        return isset($this->relationErrors[$relationName])?$this->relationErrors[$relationName]:null;
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

                return;
            }
        }

        if (in_array($key, $this->getDates()) && $value) {
            if (!$value instanceof Carbon && ! $value instanceof \DateTime) {
                $value = new Carbon($value);
                $this->attributes[$key] = $value;
                return;
            }
        }

        parent::setAttribute($key, $value);

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
     * @throws \Exception
     */
    public function push()
    {
        $validationError = false;

        try {
            $this->save();
        } catch (ValidationException $e) {
            $validationError = true;
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

        //we need to differentiate collection saving and single model saving
        //as they rise different exceptions with different logic
        //1) thus we need to store those exceptions ($this->relationErrors)
        //2) we need also to add current error, to errors stack ($this->errors)
        //
        //$this->relationErrors for ChildEntity
        //$this->errors for ParentEntity

        foreach ($this->relations as $key => $models) {
            $relation = static::$relationsCache[$this->getRelationCacheKey($key)];

            if ($this->isCollection($models)) {
                /** @var Collection $models */
                $modelsArray = $models->all(true);
                $error = false;
                $errors = [];
                foreach (array_filter($modelsArray) as $model) {
                    /** @var BaseModel $model */
                    $model->preserveKeys($relation);
                    try {
                        $model->push();
                        $errors[] = null;
                    } catch (ValidationException $e) {
                        $errors[] = $e;
                        $error = true;
                        $validationError = true;
                    }
                }
                if ($error) {
                    $this->relationErrors[$key] = new ValidationExceptionCollection($errors);
                    $this->errors->add($key, $errors);
                }
            } elseif ($models) {
                /** @var BaseModel $models */
                $models->preserveKeys($relation);
                try {
                    $models->push();
                } catch (ValidationException $e) {
                    $this->errors->add($key, $models->getErrors());
                    $this->relationErrors[$key] = $e;
                    $validationError = true;
                }
            }
        }

        if ($validationError) {
            throw new ValidationException($this->getErrors());
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

        return parent::save($options);
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
        if ($relation instanceof Relation) {
            static::$relationsCache[$this->getRelationCacheKey($relationName)] = $relation;
        }

        return parent::setRelation($relationName, $value);
    }

    /**
     * @param mixed $id
     * @return BaseModel
     * @throws ModelNotFoundException
     */
    public function findByIdentifier($id)
    {
        return $this->findOrFail($id);
    }

    /**
     * @param $method
     * @return string
     */
    protected function getRelationCacheKey($method)
    {
        return spl_object_hash($this).'_'.$method;
    }


    /**
     * Create a collection of models from a request collection
     * The method is more efficient if is passed a Collection of existing entries otherwise it will do a query for every entity
     * @param array $requestCollection
     * @param EloquentCollection|null $existingModels
     * @return Collection
     */
    public function hydrateRequestCollection(array $requestCollection, EloquentCollection $existingModels = null)
    {
        $keyName = $this->getKeyName();
        $models = array_map(function ($item) use ($keyName, $existingModels) {

            $model = null;
            $entityId = $item[$keyName];

            if ($existingModels) {
                //get the model from the collection, or create a new instance
                $model = $existingModels->get($entityId, function () { //using a closure, so new instance is only created when the default is required
                    return $this->newInstance();
                });
            } else {
                $this->findOrNew($entityId);
            }

            $model->fill($item);

            return $model;
        }, $requestCollection);

        return $this->newCollection($models);
    }


    /**
     * Handle case where the value might be from Cabon::toArray
     * @param mixed $value
     * @return Carbon|static
     */
    protected function asDateTime($value)
    {
        if (is_array($value) && isset($value['date'])) {
            return Carbon::parse($value['date'], $value['timezone']);
        }

        return parent::asDateTime($value);
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        // Run the parent cast rules in the parent method
        $value = parent::castAttribute($key, $value);

        if (is_null($value)) {
            return $value;
        }

        switch ($this->getCastType($key)) {
            case 'date':
                if (is_array($value)){
                    return $this->asDateTime($value);
                }
                return Carbon::createFromFormat('Y-m-d', $value);
            case 'datetime':
                if (is_array($value)){
                    return $this->asDateTime($value);
                }
                return Carbon::createFromFormat('Y-m-d H:i:s', $value);
            default:
                return $value;
        }
    }

}
