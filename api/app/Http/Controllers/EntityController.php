<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:37
 */

namespace App\Http\Controllers;

use App\Extensions\Controller\RequestValidationTrait;
use App\Helpers\ModelHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spira\Repository\Model\BaseModel;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Paginator\PaginatedRequestDecoratorInterface;
use Spira\Responder\Response\ApiResponse;

abstract class EntityController extends ApiController
{
    use RequestValidationTrait;

    protected $validateRequestRule = 'uuid';

    /**
     * @var BaseModel
     */
    protected $model;

    public function __construct(BaseModel $model, TransformerInterface $transformer)
    {
        $this->model = $model;
        parent::__construct($transformer);
    }

    /**
     * Get all entities.
     *
     * @return ApiResponse
     */
    public function getAll()
    {
        return $this->getResponse()
            ->transformer($this->transformer)
            ->collection($this->getAllEntities());
    }

    public function getAllPaginated(PaginatedRequestDecoratorInterface $request)
    {
        $count = $this->countEntities();
        $limit = $request->getLimit($this->paginatorDefaultLimit, $this->paginatorMaxLimit);
        $offset = $request->isGetLast()?$count-$limit:$request->getOffset();
        $collection = $this->getAllEntities($limit,$offset);

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
        $this->validateId($id, $this->getModel()->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getModel()->getKeyName());
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
        $model = $this->getModel()->newInstance();
        $model->fill($request->all());
        $model->push();

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
        $this->validateId($id, $this->getModel()->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getEntityById($id);
        } catch (ModelNotFoundException $e) {
            $model = $this->getModel()->newInstance();
        }
        $model->fill($request->all());
        $model->push();

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

        $ids = $this->getIds($requestCollection, $this->getModel()->getKeyName(), $this->validateRequestRule);
        $models = [];
        if (!empty($ids)) {
            $models = $this->getManyEntitiesByIds($ids);
        }

        $putModels = [];
        $keyName = $this->getModel()->getKeyName();
        foreach ($requestCollection as $requestEntity) {
            $id = isset($requestEntity[$keyName])?$requestEntity[$keyName]:null;
            if ($id && !empty($models) && $models->has($id)) {
                $model = $models->get($id);
            } else {
                $model = $this->getModel()->newInstance();
            }
            /** @var BaseModel $model */
            $model->fill($requestEntity);
            $putModels[] = $model;
        }

        ModelHelper::saveMany($putModels);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdCollection($putModels);
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
        $this->validateId($id, $this->getModel()->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getModel()->getKeyName());
        }

        $model->fill($request->all());
        $model->push();

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
        $ids = $this->getIds($requestCollection, $this->getModel()->getKeyName(), $this->validateRequestRule);
        $models = $this->getManyEntitiesByIds($ids);
        if ($models->count() !== count($ids)) {
            throw $this->notFoundManyException($ids, $models, $this->getModel()->getKeyName());
        }

        foreach ($requestCollection as $requestEntity) {
            $id = $requestEntity[$this->getModel()->getKeyName()];
            $model = $models->get($id);

            /** @var BaseModel $model */
            $model->fill($requestEntity);
        }

        ModelHelper::saveMany($models->all());

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
        $this->validateId($id, $this->getModel()->getKeyName(), $this->validateRequestRule);

        try {
            $model = $this->getEntityById($id);
            $model->delete();
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getModel()->getKeyName());
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
        $ids = $this->getIds($requestCollection, $this->getModel()->getKeyName(), $this->validateRequestRule);
        $models = $this->getManyEntitiesByIds($ids);

        if (count($ids) !== $models->count()) {
            throw $this->notFoundManyException($ids, $models, $this->getModel()->getKeyName());
        }

        ModelHelper::deleteMany($models->all());

        return $this->getResponse()->noContent();
    }

    /**
     * @param $id
     * @return BaseModel
     * @throws ModelNotFoundException
     */
    protected function getEntityById($id)
    {
        return $this->getModel()->findOrFail($id);
    }

    protected function countEntities()
    {
        return $this->getModel()->count();
    }

    protected function getAllEntities($limit = null, $offset = null)
    {
        return $this->getModel()->take($limit)->skip($offset)->get();
    }

    protected function getManyEntitiesByIds($ids, $limit = null, $offset = null)
    {
        return $this->getModel()->take($limit)->skip($offset)->findMany($ids);
    }

    protected function getModel()
    {
        return $this->model;
    }
}
