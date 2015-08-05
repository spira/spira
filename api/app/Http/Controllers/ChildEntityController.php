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
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

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
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);
        $this->validateId($childId, $childKey, $this->validateChildRequestRule);

        $relation->getBaseQuery()->whereIn($childKey,$childId);
        $childModel = $relation->getResults();
        if (!$childModel){
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
     */
    public function postOne($id, Request $request)
    {
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

        $relation = $this->getRelation($model);
        $childModel = $relation->getQuery()->getModel()->newInstance();
        $childModel->fill($request->all());
        $model->setRelation($this->relationName,$childModel, $relation);

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
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);
        $this->validateId($childId, $childKey, $this->validateChildRequestRule);

        $relation->getBaseQuery()->whereIn($childKey, $childId);
        $childModel = $relation->getResults();

        if (!$childModel){
            $childModel = $relation->getQuery()->getModel()->newInstance();
        }

        $childModel->fill($request->all());
        $this->getRepository()->save($model);

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

        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);

        $ids = $this->getIds($requestCollection, $childKey, $this->validateChildRequestRule);
        $childModels = [];
        if (!empty($ids)) {
            $relation->getBaseQuery()->whereIn($childKey,$ids);
            $childModels = $relation->getResults();
        }

        $putModels = [];
        foreach ($requestCollection as $requestEntity) {
            $id = isset($requestEntity[$childKey])?$requestEntity[$childKey]:null;
            if ($id && !empty($childModels) && $childModels->has($id)) {
                $childModel = $childModels->get($id);
            } else {
                $childModel = $relation->getQuery()->getModel()->newInstance();;
            }
            /** @var BaseModel $childModel */
            $childModel->fill($requestEntity);
            $putModels[] = $childModel;
        }
        $putModels = $relation->getQuery()->getModel()->newCollection($putModels);

        $model->setRelation($this->relationName,$putModels,$relation);

        $this->getRepository()->save($model);

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
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);

        $this->validateId($childId, $childKey, $this->validateChildRequestRule);

        $relation->getBaseQuery()->whereIn($childKey,$childId);
        $childModel = $relation->getResults();

        if (!$childModel){
            throw $this->notFoundException($this->getKeyName());
        }

        $childModel->fill($request->all());
        $this->getRepository()->save($model);

        return $this->getResponse()->noContent();
    }

//    /**
//     * Patch many entites.
//     *
//     * @param  Request  $request
//     * @return ApiResponse
//     */
//    public function patchMany(Request $request)
//    {
//        $requestCollection = $request->data;
//        $ids = $this->getIds($requestCollection, $this->getKeyName(), $this->validateRequest, $this->validateRequestRule);
//        $models = $this->getRepository()->findMany($ids);
//        if ($models->count() !== count($ids)) {
//            throw $this->notFoundManyException($ids, $models, $this->getKeyName());
//        }
//
//        foreach ($requestCollection as $requestEntity) {
//            $id = $requestEntity[$this->getKeyName()];
//            $model = $models->get($id);
//
//            /** @var BaseModel $model */
//            $model->fill($requestEntity);
//        }
//
//        $this->getRepository()->saveMany($models);
//
//        return $this->getResponse()->noContent();
//    }

    /**
     * Delete an entity.
     *
     * @param  string $id
     * @param string $childId
     * @return ApiResponse
     */
    public function deleteOne($id, $childId)
    {
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

        $relation = $this->getRelation($model);
        $childKey = $this->getChildKey($relation);
        $this->validateId($childId, $childKey, $this->validateChildRequestRule);

        $relation->getBaseQuery()->whereIn($childKey, $childId);
        $childModel = $relation->getResults();

        if (!$childModel){
            throw $this->notFoundException($this->getKeyName());
        }

        /** @var BaseModel $childModel */
        $childModel->markAsDeleted();
        $this->getRepository()->save($model);

        return $this->getResponse()->noContent();
    }

//    /**
//     * Delete many entites.
//     *
//     * @param  Request  $request
//     * @return ApiResponse
//     */
//    public function deleteMany(Request $request)
//    {
//        $requestCollection = $request->data;
//        $ids = $this->getIds($requestCollection, $this->getKeyName(), $this->validateRequest, $this->validateRequestRule);
//        $models = $this->getRepository()->findMany($ids);
//
//        if (count($ids) !== $models->count()) {
//            throw $this->notFoundManyException($ids, $models, $this->getKeyName());
//        }
//
//        $this->getRepository()->deleteMany($models);
//        return $this->getResponse()->noContent();
//    }

    /**
     * @param BaseModel $model
     * @return HasOneOrMany
     */
    public function getRelation(BaseModel $model)
    {
        return $model->{$this->relationName}();
    }

    /**
     * @param Relation $relation
     * @return string
     */
    public function getChildKey(Relation $relation)
    {
        return $relation->getQuery()->getModel()->getKeyName();
    }
}