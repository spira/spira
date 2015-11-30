<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\Model\Model;

use Illuminate\Database\Eloquent\FactoryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class ModelFactoryInstance implements Arrayable, Jsonable
{
    private $transformerService;
    /**
     * @var FactoryBuilder
     */
    private $factoryInstance;
    private $customizations = [];
    private $entityCount = 1;
    private $transformer;
    private $makeVisible;
    private $showOnly;
    private $hide;
    private $entityType;
    private $appends = [];
    /** @var  Collection|BaseModel */
    private $predefinedEntities;
    /** @var  Collection|BaseModel */
    private $loadedEntities;

    /**
     * New model instance.
     *
     * @param $factoryInstance
     * @param $transformerService
     */
    public function __construct($factoryInstance, $transformerService)
    {
        $this->factoryInstance = $factoryInstance;
        $this->transformerService = $transformerService;
    }

    /**
     * Set number of entities to create (not required, default is 1).
     *
     * @param $number
     *
     * @return $this
     */
    public function count($number)
    {
        $this->entityCount = $number;

        return $this;
    }

    /**
     * Set custom value overrides for the entity.
     *
     * @param $customizations
     *
     * @return $this
     */
    public function customize(array $customizations)
    {
        $this->customizations = array_merge($this->customizations, $customizations);

        return $this;
    }

    /**
     * Make otherwise hidden parameters visible.
     *
     * @param array $makeVisible
     *
     * @return $this
     */
    public function makeVisible(array $makeVisible)
    {
        $this->makeVisible = $makeVisible;

        return $this;
    }

    /**
     * Limit what properties the factory instance returns.
     *
     * @param $showOnly
     *
     * @return $this
     */
    public function showOnly(array $showOnly)
    {
        $this->showOnly = $showOnly;

        return $this;
    }

    /**
     * Hide attributes the factory instance returns.
     *
     * @param  array $hide
     * @return $this
     */
    public function hide(array $hide)
    {
        $this->hide = $hide;

        return $this;
    }

    /**
     * Add properties to the returned entity.
     *
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function append($key, $value)
    {
        $this->appends[$key] = $value;

        return $this;
    }

    /**
     * Make the model factory use a custom transformer
     * eg
     * $factory->get(\App\Models\UserCredential::class)
     *      ->setTransformer(\App\Http\Transformers\UserTransformer::class)
     *      ->toArray();.
     *
     * @param $transformerName
     *
     * @return $this
     */
    public function setTransformer($transformerName)
    {
        $this->transformer = new $transformerName($this->transformerService);

        return $this;
    }

    /**
     * Define a model to operate on, rather than using the factory.
     * @param BaseModel $model
     * @return $this
     */
    public function setModel(BaseModel $model)
    {
        $this->predefinedEntities = $model;

        return $this->count(1);
    }

    /**
     * Define a collection to operate on, rather than using the factory.
     * @param Collection $collection
     * @return $this
     */
    public function setCollection(Collection $collection)
    {
        $collection = $collection->map(function ($item) {
            if ($item instanceof BaseModel) {
                return $item;
            }

            return new DataModel($item);
        });

        $this->predefinedEntities = $collection;

        return $this->count($collection->count());
    }

    /**
     * Get the built entities.
     *
     * @return Collection|BaseModel
     */
    private function built()
    {
        if ($this->loadedEntities) {
            return $this->loadedEntities;
        }

        $this->loadedEntities = $this->getPredefinedOrMocks();

        $this->setEntityType();

        return $this->loadedEntities;
    }

    /**
     * Set if the entity is a single item or a collection.
     *
     * @return void
     */
    protected function setEntityType()
    {
        $this->entityType = ($this->entityCount > 1) ? 'collection' : 'item';
    }

    /**
     * Modify an entity.
     *
     * @param BaseModel $entity
     * @return BaseModel
     */
    private function modifyEntity(BaseModel $entity)
    {
        if ($this->showOnly) {
            $attributes = $entity->getAttributes();

            $newHidden = array_diff(array_keys($attributes), $this->showOnly);
            $entity->setHidden($newHidden);
        }

        if ($this->makeVisible) {
            $hidden = $entity->getHidden();

            $newHidden = array_diff($hidden, $this->makeVisible);

            $entity->setHidden($newHidden);
        }

        if ($this->hide) {
            $hidden = $entity->getHidden();

            $newHidden = array_merge($hidden, $this->hide);

            $entity->setHidden($newHidden);
        }

        if ($this->customizations) {
            foreach ($this->customizations as $key => $value) {
                $entity->{$key} = $value;
            }
        }

        if (! empty($this->appends)) {
            foreach ($this->appends as $appendKey => $appendValue) {
                $entity->{$appendKey} = $appendValue;
            }
        }

        return $entity;
    }

    /**
     * Get the modified entity[ies].
     *
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
                $entity = $entity->each(function ($singleEntity) {
                    return $this->modifyEntity($singleEntity);
                });
                break;
        }

        return $entity;
    }

    /**
     * Get the transformed entity[ies].
     *
     * @return mixed
     */
    public function transformed()
    {
        $entity = $this->modified();

        if (! $this->transformer) {
            $this->transformer = new EloquentModelTransformer($this->transformerService);
        }

        $method = 'transform'.ucfirst($this->entityType);
        $transformedEntity = $this->transformer->{$method}($entity);

        $transformedEntity = array_except(
            $transformedEntity,
            $this->hide
        ); //allow the definer to specify transformed values to hide

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
     * Get the JSON encoded string of the (built, modified, transformed) entity[ies].
     *
     * @param int $options
     *
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

    /**
     * Create a collection of models.
     * Shortcut for FactoryBuilder.
     * @param  array $attributes
     * @return BaseModel|Collection
     */
    public function make(array $attributes = [])
    {
        $this->customize($attributes);

        return $this->modified();
    }

    /**
     * Create a collection of models and persist them to the database.
     *
     * @param  array $attributes
     * @return Collection|BaseModel
     */
    public function create(array $attributes = [])
    {
        $results = $this->make($attributes);

        if ($this->entityCount === 1) {
            $results->save();
        } else {
            foreach ($results as $result) {
                $result->save();
            }
        }

        return $results;
    }

    /**
     * @param $count
     * @return BaseModel|Collection
     * @internal param array $attributes
     */
    private function getModelMock($count = 1)
    {
        if (is_null($this->factoryInstance)) {
            throw new \LogicException('No factory class passed to model factory, cannot generate a mock');
        }

        $entity = $this->factoryInstance
            ->times($count)
            ->make($this->customizations);

        return $entity;
    }

    /**
     * Get either the predefined (subset of) collection/model, or.
     * @return Collection|mixed|BaseModel
     */
    private function getPredefinedOrMocks()
    {
        if ($this->entityCount > 1) {
            $collection = new Collection();

            if ($this->predefinedEntities) {
                if ($this->predefinedEntities instanceof Collection) {
                    $collection = $collection->merge($this->predefinedEntities->random($this->entityCount));
                } else {
                    $collection->push($this->predefinedEntities);
                }
            }

            if ($collection->count() < $this->entityCount) {
                /** @var Collection $collection */
                $collection = $collection->merge($this->getModelMock($this->entityCount - $collection->count()));
            }

            return $collection;
        } else {
            if ($this->predefinedEntities) {
                if ($this->predefinedEntities instanceof Collection) {
                    return $this->predefinedEntities->random();
                } else {
                    return $this->predefinedEntities;
                }
            }

            return $this->getModelMock();
        }
    }
}
