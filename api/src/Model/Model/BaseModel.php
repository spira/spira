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
     * Get the table name for the instance
     * @return string
     */
    public static function getTableName()
    {
        return with(new static())->getTable();
    }

    /**
     * Get the primary key name for the instance
     * @return string
     */
    public static function getPrimaryKey()
    {
        return with(new static())->getKeyName();
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws SetRelationException
     */
    public function setAttribute($key, $value)
    {
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
     * @param mixed $id
     * @return BaseModel
     * @throws ModelNotFoundException
     */
    public function findByIdentifier($id)
    {
        return $this->findOrFail($id);
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
