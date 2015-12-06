<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Spira\Model\Model\BaseModel;
use Spira\Model\Collection\Collection;
use Illuminate\Database\Eloquent\Builder;
use Spira\Responder\Contract\TransformerInterface;
use App\Extensions\Controller\RequestValidationTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

abstract class AbstractRelatedEntityController extends ApiController
{
    use RequestValidationTrait;

    protected $relationName = null;

    /**
     * Override this property to provide default pivot values.
     */
    protected $defaultPivotValues = [];

    /**
     * @var BaseModel
     */
    protected $cacheChildModel;

    /**
     * @var BaseModel
     */
    protected $parentModel;

    public function __construct(BaseModel $parentModel, TransformerInterface $transformer)
    {
        $this->parentModel = $parentModel;

        if (! $this->relationName) {
            throw new \InvalidArgumentException('You must specify relationName in '.static::class);
        }

        if (! method_exists($parentModel, $this->relationName)) {
            throw new \InvalidArgumentException(
                'Relation '.$this->relationName.', required by '.
                static::class.', does not exist in '.get_class($parentModel)
            );
        }

        parent::__construct($transformer);
    }

    /**
     * Function called before child models are synced with parent.
     *
     * @param $model
     * @param $parent
     */
    protected function preSync(BaseModel $parent, Collection $children)
    {
    }

    /**
     * Function called before response is sent after the model has been updated via sync.
     *
     * @param $model
     * @param $parent
     */
    protected function postSync(BaseModel $parent, Collection $children)
    {
    }

    /**
     * Override this method to provide custom pivot validation rules.
     *
     * @param null $entityId
     * @return array
     */
    protected function getValidationRules($entityId = null)
    {
        $childRules = $this->getChildModel()->getValidationRules($entityId);
        $pivotRules = $this->getPivotValidationRules();

        return array_merge($childRules, $pivotRules);
    }

    /**
     * Override this method to provide custom validation rules.
     *
     * @return array
     */
    protected function getPivotValidationRules()
    {
        return [];
    }

    /**
     * @param BaseModel $parentModel
     * @return BelongsToMany|Builder
     */
    protected function getRelation(BaseModel $parentModel)
    {
        return $parentModel->{$this->relationName}();
    }

    /**
     * @return BaseModel
     */
    public function getParentModel()
    {
        return $this->parentModel;
    }

    /**
     * @return BaseModel
     */
    public function getChildModel()
    {
        if (is_null($this->cacheChildModel)) {
            $this->cacheChildModel = $this->getRelation($this->parentModel)->getRelated();
        }

        return $this->cacheChildModel;
    }

    /**
     * @param $id
     * @return BaseModel
     */
    protected function findParentEntity($id)
    {
        try {
            return $this->getParentModel()->findByIdentifier($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }
    }

    /**
     * @param $id
     * @param BaseModel $parent
     * @return BaseModel
     */
    protected function findOrNewChildEntity($id, BaseModel $parent)
    {
        try {
            return $this->getRelation($parent)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $this->getChildModel()->newInstance();
        }
    }

    /**
     * @param $id
     * @param BaseModel $parent
     * @return BaseModel
     */
    protected function findOrFailChildEntity($id, BaseModel $parent)
    {
        try {
            return $this->getRelation($parent)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getChildModel()->getKeyName());
        }
    }

    /**
     * @param $parent
     * @return Collection
     */
    protected function findAllChildren($parent)
    {
        return $this->getRelation($parent)->getResults();
    }

    /**
     * @param $requestCollection
     * @param BaseModel $parent
     * @return Collection
     */
    protected function findChildrenCollection($requestCollection, BaseModel $parent)
    {
        $ids = $this->getIds($requestCollection, $this->getChildModel()->getKeyName());
        $models = $this->getRelation($parent)->findMany($ids);

        return $models;
    }

    /**
     * @param $requestCollection
     * @param BaseModel $parent
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function findOrFailChildrenCollection($requestCollection, BaseModel $parent)
    {
        $ids = $this->getIds($requestCollection, $this->getChildModel()->getKeyName());

        if (empty($ids)) {
            $models = $this->getChildModel()->newCollection();
            throw $this->notFoundManyException($ids, $models, $this->getChildModel()->getKeyName());
        }

        $models = $this->getRelation($parent)->findMany($ids);

        if ($models && count($ids) !== $models->count()) {
            throw $this->notFoundManyException($ids, $models, $this->getChildModel()->getKeyName());
        }

        return $models;
    }

    /**
     * @param Collection $childModels
     * @param Collection|array $requestCollection
     * @return array
     */
    protected function makeSyncList(Collection $childModels, $requestCollection)
    {
        $childPk = $this->getChildModel()->getPrimaryKey();

        if (! ($requestCollection instanceof Collection)) {
            $requestCollection = new Collection($requestCollection);
        }

        $childModels->keyBy($childPk);
        $requestCollection->keyBy($childPk);

        $relations = [];
        foreach ($childModels as $model) {
            $key = $model->{$childPk};
            $values = $this->getPivotValues($requestCollection[$key]);

            if (! empty($values)) {
                $relations[$key] = $values;
            } else {
                $relations[] = $key;
            }
        }

        return $relations;
    }

    /**
     * Override this method to provide custom pivot values.
     *
     * @param array $requestEntity
     * @return array
     */
    protected function getPivotValues($requestEntity = null)
    {
        $values = $this->defaultPivotValues;

        if (! empty($requestEntity['_pivot'])) {
            $values = array_merge($values, $requestEntity['_pivot']);
        }

        return $values;
    }

    /**
     * @param Collection $collection
     * @return Collection
     */
    protected function saveNewItemsInCollection(Collection $collection)
    {
        return $collection->each(
            function (BaseModel $model) {
                if (! $model->exists || $model->isDirty()) {
                    $model->save();
                }
            }
        );
    }

    /**
     * @param $childId
     * @param BaseModel $parentModel
     * @return bool
     */
    protected function childIdCanFallbackToParent($childId, BaseModel $parentModel)
    {
        $fk = $this->getRelation($parentModel)->getForeignKey();
        $parentKey = $parentModel->getKeyName();

        return $childId === false && ends_with($fk, $parentKey);
    }
}
