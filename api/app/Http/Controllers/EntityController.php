<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:37
 */

namespace App\Http\Controllers;

use App\Extensions\Controller\RequestValidationTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spira\Repository\Model\BaseModel;
use Spira\Responder\Paginator\PaginatedRequestDecoratorInterface;
use Spira\Responder\Response\ApiResponse;

abstract class EntityController extends ApiController
{
    use RequestValidationTrait;

    /**
     * Get all entities.
     *
     * @return ApiResponse
     */
    public function getAll()
    {
        return $this->getResponse()
            ->transformer($this->transformer)
            ->collection($this->getRepository()->all());
    }

    public function getAllPaginated(PaginatedRequestDecoratorInterface $request)
    {
        $count = $this->getRepository()->count();
        $limit = $request->getLimit($this->paginatorDefaultLimit, $this->paginatorMaxLimit);
        $offset = $request->isGetLast()?$count-$limit:$request->getOffset();
        $collection = $this->getRepository()->all(['*'], $offset, $limit);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->paginatedCollection($collection, $offset, $count);
    }

    /**
     * Get one entity.
     *
     * @param  string $id
     * @return ApiResponse
     */
    public function getOne($id)
    {
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($model)
        ;
    }

    /**
     * Post a new entity.
     *
     * @param  Request $request
     * @return ApiResponse
     */
    public function postOne(Request $request)
    {
        $model = $this->getRepository()->getNewModel();
        $model->fill($request->all());
        $this->getRepository()->save($model);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdItem($model);
    }

    /**
     * Put an entity.
     *
     * @param  string   $id
     * @param  Request  $request
     * @return ApiResponse
     */
    public function putOne($id, Request $request)
    {
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            $model = $this->getRepository()->getNewModel();
        }
        $model->fill($request->all());
        $this->getRepository()->save($model);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdItem($model);
    }

    /**
     * Put many entities.
     *
     * @param  Request  $request
     * @return ApiResponse
     */
    public function putMany(Request $request)
    {
        $requestCollection = $request->data;

        $ids = $this->getIds($requestCollection, $this->getKeyName(), $this->validateRequestRule);
        $models = [];
        if (!empty($ids)) {
            $models = $this->getRepository()->findMany($ids);
        }

        $putModels = [];
        foreach ($requestCollection as $requestEntity) {
            $id = isset($requestEntity[$this->getKeyName()])?$requestEntity[$this->getKeyName()]:null;
            if ($id && !empty($models) && $models->has($id)) {
                $model = $models->get($id);
            } else {
                $model = $this->getRepository()->getNewModel();
            }
            /** @var BaseModel $model */
            $model->fill($requestEntity);
            $putModels[] = $model;
        }

        $models = $this->getRepository()->saveMany($putModels);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdCollection($models);
    }

    /**
     * Patch an entity.
     *
     * @param  string   $id
     * @param  Request  $request
     * @return ApiResponse
     */
    public function patchOne($id, Request $request)
    {
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

        $model->fill($request->all());
        $this->getRepository()->save($model);

        return $this->getResponse()->noContent();
    }

    /**
     * Patch many entites.
     *
     * @param  Request  $request
     * @return ApiResponse
     */
    public function patchMany(Request $request)
    {
        $requestCollection = $request->data;
        $ids = $this->getIds($requestCollection, $this->getKeyName(), $this->validateRequestRule);
        $models = $this->getRepository()->findMany($ids);
        if ($models->count() !== count($ids)) {
            throw $this->notFoundManyException($ids, $models, $this->getKeyName());
        }

        foreach ($requestCollection as $requestEntity) {
            $id = $requestEntity[$this->getKeyName()];
            $model = $models->get($id);

            /** @var BaseModel $model */
            $model->fill($requestEntity);
        }

        $this->getRepository()->saveMany($models);

        return $this->getResponse()->noContent();
    }

    /**
     * Delete an entity.
     *
     * @param  string   $id
     * @return ApiResponse
     */
    public function deleteOne($id)
    {
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getRepository()->find($id);
            $this->getRepository()->delete($model);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

        return $this->getResponse()->noContent();
    }

    /**
     * Delete many entites.
     *
     * @param  Request  $request
     * @return ApiResponse
     */
    public function deleteMany(Request $request)
    {
        $requestCollection = $request->data;
        $ids = $this->getIds($requestCollection, $this->getKeyName(), $this->validateRequestRule);
        $models = $this->getRepository()->findMany($ids);

        if (count($ids) !== $models->count()) {
            throw $this->notFoundManyException($ids, $models, $this->getKeyName());
        }

        $this->getRepository()->deleteMany($models);
        return $this->getResponse()->noContent();
    }



}
