<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spira\Model\Model\BaseModel;
use Spira\Model\Collection\Collection;
use Illuminate\Database\Eloquent\Builder;
use Spira\Rbac\Access\AuthorizesRequestsTrait;
use Spira\Responder\Contract\TransformerInterface;
use App\Extensions\Controller\RequestValidationTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spira\Responder\Response\ApiResponse;

abstract class LinkedEntityController extends ApiController
{
    use RequestValidationTrait, AuthorizesRequestsTrait;

    protected $relationName = null;

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

        if (!$this->relationName) {
            throw new \InvalidArgumentException('You must specify relationName in ' . static::class);
        }

        if (!method_exists($parentModel, $this->relationName)) {
            throw new \InvalidArgumentException(
                'Relation ' . $this->relationName . ', required by ' .
                static::class . ', does not exist in ' . get_class($parentModel)
            );
        }

        parent::__construct($transformer);
    }

    public function getAll(Request $request, $id)
    {
        $model = $this->findParentEntity($id);

        $childEntities = $this->findAllChildren($model);
        $childEntities = $this->getWithNested($childEntities, $request);

        $this->checkPermission(static::class . '@getAll', ['model' => $model, 'children' => $childEntities]);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($childEntities);
    }

    public function attachOne(Request $request, $id, $childId)
    {
        $parent     = $this->findParentEntity($id);
        $childModel = $this->findOrNewChildEntity($childId, $parent);

        $this->validateRequest($request->json()->all(), $this->getValidationRules($childId));
        $childModel->fill($request->json()->all());
        $this->checkPermission(static::class . '@attachOne', ['model' => $parent, 'children' => $childModel]);

        $this->getRelation($parent)->attach($childModel, $this->getPivotValues($childModel));

        return $this->getResponse()->created();
    }

    public function detachOne($id, $childId)
    {
        $parent     = $this->findParentEntity($id);
        $childModel = $this->findOrFailChildEntity($childId, $parent);

        $this->checkPermission(static::class . '@detachOne', ['model' => $parent, 'children' => $childModel]);
        $this->getRelation($parent)->detach($childModel);

        return $this->getResponse()->noContent();
    }

    public function attachMany(Request $request, $id)
    {
        return $this->processMany($request, $id, 'attach');
    }

    public function syncMany(Request $request, $id)
    {
        return $this->processMany($request, $id, 'sync');
    }

    protected function processMany(Request $request, $id, $method)
    {
        $parent = $this->findParentEntity($id);

        $requestCollection = $request->json()->all();
        $this->validateRequestCollection($requestCollection, $this->getChildModel());

        $relations        = [];
        $existingChildren = $this->findChildrenCollection($requestCollection, $parent);
        $childModels      = $this->getChildModel()->hydrateRequestCollection($requestCollection, $existingChildren);
        $childPk          = $this->getChildModel()->getPrimaryKey();

        $this->checkPermission(static::class . '@' . $method . 'All', ['model' => $parent, 'children' => $childModels]);

        /** @var $model BaseModel */
        foreach ($childModels as $model) {
            if (!$model->exists || $model->isDirty()) {
                echo 'FUFUF';
                $model->save();
            }

            $key    = $model->{$childPk};
            $values = $this->getPivotValues($model);

            if (!empty($values)) {
                $relations[$key] = $values;
            } else {
                $relations[] = $key;
            }
        }

        $this->getRelation($parent)->{$method}($relations);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($this->findAllChildren($parent), ApiResponse::HTTP_CREATED);
    }

    protected function getValidationRules($entityId = null)
    {
        return [];
    }

    protected function getPivotValues(BaseModel $entity = null)
    {
        return $this->defaultPivotValues;
    }

    //
    // TODO refactor copy-paste below
    //

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
        $ids    = $this->getIds($requestCollection, $this->getChildModel()->getKeyName());
        $models = $this->getRelation($parent)->findMany($ids);

        return $models;
    }

}