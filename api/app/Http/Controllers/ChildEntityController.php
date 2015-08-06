<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 04.08.15
 * Time: 20:23
 */

namespace App\Http\Controllers;

use App\Extensions\Controller\RequestValidationTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Spira\Repository\Model\BaseModel;
use Spira\Repository\Validation\ValidationException;
use Spira\Responder\Response\ApiResponse;

class ChildEntityController extends ApiController
{
    use RequestValidationTrait;

    protected $validateChildRequestRule = 'uuid';

    protected $relationName = null;


    /**
     * Get all entities.
     *
     * @param string $id
     * @return ApiResponse
     */
    public function getAll($id)
    {
        $model = $this->getParentModel($id);

        $childModels = $model->{$this->relationName};

        return $this->getResponse()
            ->transformer($this->transformer)
            ->collection($childModels);
    }

    /**
     * Get one entity.
     *
     * @param  string $id
     * @param string $childId
     * @return ApiResponse
     */
    public function getOne($id, $childId)
    {
        $model = $this->getParentModel($id);

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);
        $this->validateId($childId, $childKey, $this->validateChildRequestRule);

        $relation->getBaseQuery()->whereIn($childKey, [$childId]);
        $childModel = $relation->getResults()->first();
        if (!$childModel) {
            throw $this->notFoundException($childKey);
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($childModel)
            ;
    }

    /**
     * Post a new entity.
     *
     * @param string $id
     * @param  Request $request
     * @return ApiResponse
     * @throws \Exception
     * @throws \Exception|null
     * @throws \Spira\Repository\Repository\RepositoryException
     */
    public function postOne($id, Request $request)
    {
        $model = $this->getParentModel($id);

        $relation = $this->getRelation($model);
        $childModel = $relation->getQuery()->getModel()->newInstance();
        $childModel->fill($request->all());

        $model->setRelation($this->relationName, $childModel, $relation);
        $this->saveModel($model);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdItem($childModel);
    }

    /**
     * Put an entity.
     *
     * @param  string $id
     * @param $childId
     * @param  Request $request
     * @return ApiResponse
     */
    public function putOne($id, $childId, Request $request)
    {
        $model = $this->getParentModel($id);

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);
        $this->validateId($childId, $childKey, $this->validateChildRequestRule);

        $relation->getBaseQuery()->whereIn($childKey, [$childId]);
        $childModel = $relation->getResults()->first();

        if (!$childModel) {
            $childModel = $relation->getQuery()->getModel()->newInstance();
        }

        /** @var BaseModel $childModel */
        $childModel->fill($request->all());

        $model->setRelation($this->relationName, $childModel, $relation);
        $this->saveModel($model);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdItem($childModel);
    }

    /**
     * Put many entities.
     *
     * @param string $id
     * @param  Request $request
     * @return ApiResponse
     */
    public function putMany($id, Request $request)
    {
        $requestCollection = $request->data;

        $model = $this->getParentModel($id);

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);

        $ids = $this->getIds($requestCollection, $childKey, $this->validateChildRequestRule);
        $childModels = [];
        if (!empty($ids)) {
            $relation->getBaseQuery()->whereIn($childKey, $ids);
            $childModels = $relation->getResults();
        }

        $putModels = [];
        foreach ($requestCollection as $requestEntity) {
            $id = isset($requestEntity[$childKey])?$requestEntity[$childKey]:null;
            if ($id && !empty($childModels) && $childModels->has($id)) {
                $childModel = $childModels->get($id);
            } else {
                $childModel = $relation->getQuery()->getModel()->newInstance();
                ;
            }
            /** @var BaseModel $childModel */
            $childModel->fill($requestEntity);
            $putModels[] = $childModel;
        }
        $putModels = $relation->getQuery()->getModel()->newCollection($putModels);

        $model->setRelation($this->relationName, $putModels, $relation);
        $this->saveModel($model);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdCollection($putModels);
    }

    /**
     * Patch an entity.
     *
     * @param  string $id
     * @param string $childId
     * @param  Request $request
     * @return ApiResponse
     */
    public function patchOne($id, $childId, Request $request)
    {
        $model = $this->getParentModel($id);

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);

        $this->validateId($childId, $childKey, $this->validateChildRequestRule);

        $relation->getBaseQuery()->whereIn($childKey, [$childId]);
        $childModel = $relation->getResults()->first();

        if (!$childModel) {
            throw $this->notFoundException($this->getKeyName());
        }

        /** @var BaseModel $childModel */
        $childModel->fill($request->all());

        $model->setRelation($this->relationName, $childModel, $relation);
        $this->saveModel($model);

        return $this->getResponse()->noContent();
    }

    /**
     * Patch many entites.
     *
     * @param string $id
     * @param  Request $request
     * @return ApiResponse
     */
    public function patchMany($id, Request $request)
    {
        $requestCollection = $request->data;

        $model = $this->getParentModel($id);

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);

        $ids = $this->getIds($requestCollection, $childKey, $this->validateChildRequestRule);
        $childModels = [];
        if (!empty($ids)) {
            $relation->getBaseQuery()->whereIn($childKey, $ids);
            $childModels = $relation->getResults();
        }

        if (!empty($childModels) && $childModels->count() !== count($ids)) {
            throw $this->notFoundManyException($ids, $childModels, $childKey);
        }

        foreach ($requestCollection as $requestEntity) {
            $id = $requestEntity[$childKey];
            $childModel = $childModels->get($id);

            /** @var BaseModel $childModel */
            $childModel->fill($requestEntity);
        }

        $model->setRelation($this->relationName, $childModels, $relation);
        $this->saveModel($model);

        return $this->getResponse()->noContent();
    }

    /**
     * Delete an entity.
     *
     * @param  string $id
     * @param string $childId
     * @return ApiResponse
     */
    public function deleteOne($id, $childId)
    {
        $model = $this->getParentModel($id);

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);
        $this->validateId($childId, $childKey, $this->validateChildRequestRule);

        $relation->getBaseQuery()->whereIn($childKey, [$childId]);
        $childModel = $relation->getResults()->first();

        if (!$childModel) {
            throw $this->notFoundException($this->getKeyName());
        }

        /** @var BaseModel $childModel */
        $childModel->markAsDeleted();
        $model->setRelation($this->relationName, $childModel, $relation);
        $this->saveModel($model);

        return $this->getResponse()->noContent();
    }

    /**
     * Delete many entites.
     *
     * @param string $id
     * @param  Request  $request
     * @return ApiResponse
     */
    public function deleteMany($id, Request $request)
    {
        $requestCollection = $request->data;
        $model = $this->getParentModel($id);

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);

        $ids = $this->getIds($requestCollection, $childKey, $this->validateChildRequestRule);
        $childModels = [];
        if (!empty($ids)) {
            $relation->getBaseQuery()->whereIn($childKey, $ids);
            $childModels = $relation->getResults();
        }

        if (!empty($childModels) && $childModels->count() !== count($ids)) {
            throw $this->notFoundManyException($ids, $childModels, $childKey);
        }

        /** @var BaseModel[] $childModels */
        foreach ($childModels as $childModel) {
            $childModel->markAsDeleted();
        }

        $model->setRelation($this->relationName, $childModels, $relation);
        $this->saveModel($model);

        return $this->getResponse()->noContent();
    }

    /**
     * @param $id
     * @return BaseModel
     */
    protected function getParentModel($id)
    {
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            return $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }
    }

    /**
     * @param BaseModel $model
     * @throws \Exception
     * @throws \Exception|null
     * @throws \Spira\Repository\Repository\RepositoryException
     */
    protected function saveModel(BaseModel $model)
    {
        try {
            $this->getRepository()->save($model);
        } catch (ValidationException $e) {
            if ($exception = $model->getRelationErrors($this->relationName)) {
                throw $exception;
            }

            throw $e;
        }
    }

    /**
     * @param BaseModel $model
     * @return HasOneOrMany
     */
    protected function getRelation(BaseModel $model)
    {
        return $model->{$this->relationName}();
    }

    /**
     * @param Relation $relation
     * @return string
     */
    protected function getChildKey(Relation $relation)
    {
        return $relation->getQuery()->getModel()->getKeyName();
    }
}
