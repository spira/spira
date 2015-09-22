<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Class BaseModel.
 *
 * @method static int count
 * @method static BaseModel find($id)
 * @method static BaseModel first()
 * @method static BaseModel findOrFail($id)
 * @method static Collection get
 * @method static Collection findMany($ids)
 * @method static Builder where($value,$operator,$operand)
 * @method static Builder whereIn($column,$ids)
 * @method static BaseModel skip($offset)
 * @method static BaseModel take($limit)
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

    protected $casts = [
        self::CREATED_AT => 'datetime',
        self::UPDATED_AT => 'datetime',
    ];

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
            if (! $value instanceof Carbon && ! $value instanceof \DateTime) {
                $value = new Carbon($value);
                $this->attributes[$key] = $value;

                return;
            }
        }

        parent::setAttribute($key, $value);
    }

    /**
     * Prepare value for proper assignment.
     * @param array|Collection|false|BaseModel $value
     * Can be array, empty array, null, false, Collection or Model
     * @return null|Collection|BaseModel|false  false on bad value
     */
    protected function prepareValue($value)
    {
        if (empty($value)) {
            return;
        }

        if ($this->isModel($value) || $this->isCollection($value)) {
            return $value;
        }

        if (is_array($value)) {
            $firstModel = current($value);
            if ($firstModel instanceof self) {
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
        $models = $this->isCollection($models) ? $models->all(true) : [$models];
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
        $this->save();

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
                /* @var Collection $models */
                $modelsArray = $models->all(true);
                foreach (array_filter($modelsArray) as $model) {
                    /* @var BaseModel $model */
                    $model->preserveKeys($relation);
                    $model->push();
                }
            } elseif ($models) {
                /* @var BaseModel $models */
                $models->preserveKeys($relation);
                $models->push();
            }
        }

        return true;
    }

    /**
     * Checks relation against value.
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
        return $value instanceof self;
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
     * Fires an event for RevisionableTrait.
     *
     * @param  string $event
     * @param  array  $payload
     * @param  bool   $halt
     *
     * @return mixed
     */
    public function fireRevisionableEvent($event, array $payload, $halt = true)
    {
        $event = "eloquent.{$event}: ".get_class($this);
        $method = $halt ? 'until' : 'fire';

        return static::$dispatcher->$method($event, array_merge([$this], $payload));
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
     * @return bool
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Get a relationship value from a method.
     * Relation cache added.
     *
     * @param  string  $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getRelationshipFromMethod($method)
    {
        $relations = $this->$method();

        if (! $relations instanceof Relation) {
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
     * The method is more efficient if is passed a Collection of existing entries otherwise it will do a query for every entity.
     * @param array $requestCollection
     * @param EloquentCollection|null $existingModels
     * @return Collection
     */
    public function hydrateRequestCollection(array $requestCollection, EloquentCollection $existingModels = null)
    {
        $keyName = $this->getKeyName();
        $models = array_map(function ($item) use ($keyName, $existingModels) {

            $model = null;
            $entityId = isset($item[$keyName]) ? $item[$keyName] : null;

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
     * Handle case where the value might be from Carbon::toArray.
     * @param mixed $value
     * @return Carbon|static
     */
    protected function asDateTime($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return Carbon::instance($value);
        }

        if (is_array($value) && isset($value['date'])) {
            return Carbon::parse($value['date'], $value['timezone']);
        }

        try {
            return Carbon::createFromFormat(Carbon::ISO8601, $value); //try decode ISO8601 date
        } catch (\InvalidArgumentException $e) {
            return parent::asDateTime($value);
        }
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

                try {
                    return $this->asDateTime($value); //otherwise try the alternatives
                } catch (\InvalidArgumentException $e) {
                    return Carbon::createFromFormat('Y-m-d', $value); //if it is the true base ISO8601 date format, parse it
                }

            case 'datetime':
                return $this->asDateTime($value); //try the catchall method for date translation
            default:
                return $value;
        }
    }
}
