<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:37
 */

namespace App\Http\Controllers;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spira\Repository\Model\BaseModel;
use Spira\Responder\Responder\ApiResponder;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiController
{
    /**
     * Model Repository.
     *
     * @var BaseRepository
     */
    protected $repository;

    /**
     * @var ApiResponder
     */
    protected $responder;

    /**
     * Get all entities.
     *
     * @return Response
     */
    public function getAll()
    {
        return $this->getResponder()->collection($this->getRepository()->all());
    }

    /**
     * Get one entity.
     *
     * @param  string $id
     * @return Response
     */
    public function getOne($id)
    {
        try {
            $model = $this->getRepository()->find($id);
            return $this->getResponder()->item($model);
        } catch (ModelNotFoundException $e) {
            $this->getResponder()->errorNotFound();
        }

        return $this->getResponder()->noContent();
    }

    /**
     * Post a new entity.
     *
     * @param  Request $request
     * @return Response
     */
    public function postOne(Request $request)
    {
        $model = $this->getRepository()->getNewModel();
        $model->fill($request->all());
        $this->getRepository()->save($model);

        return $this->getResponder()->createdItem($model);
    }

    /**
     * Put an entity.
     *
     * @param  string   $id
     * @param  Request  $request
     * @return Response
     */
    public function putOne($id, Request $request)
    {
        try {
            $model = $this->getRepository()->find($id);
        } catch (ModelNotFoundException $e) {
            $model = $this->getRepository()->getNewModel();
        }
        $model->fill($request->all());
        $this->getRepository()->save($model);

        return $this->getResponder()->createdItem($model);
    }

    /**
     * Put many entities.
     *
     * @param  Request  $request
     * @return Response
     */
    public function putMany(Request $request)
    {
        $requestCollection = $request->data;

        $models = $this->getRepository()->findMany($this->getIds($requestCollection));

        $putModels = [];
        foreach ($requestCollection as $requestEntity) {
            $id = $requestEntity[$this->getRepository()->getKey()];
            if ($models->has($id)) {
                $model = $models->get($id);
            } else {
                $model = $this->getRepository()->getNewModel();
            }
            /** @var BaseModel $model */
            $model->fill($requestEntity);
            $putModels[] = $model;
        }

        $models = $this->getRepository()->saveMany($putModels);

        return $this->getResponder()->createdCollection($models);
    }

    /**
     * Patch an entity.
     *
     * @param  string   $id
     * @param  Request  $request
     * @return Response
     */
    public function patchOne($id, Request $request)
    {
        $model = $this->getRepository()->find($id);
        $model->fill($request->all());
        $this->getRepository()->save($model);

        return $this->getResponder()->noContent();
    }

    /**
     * Patch many entites.
     *
     * @param  Request  $request
     * @return Response
     */
    public function patchMany(Request $request)
    {
        $requestCollection = $request->data;
        $ids = $this->getIds($requestCollection);
        $models = $this->getRepository()->findMany($ids);
        if ($models->count() !== count($ids)) {
            $this->getResponder()->errorNotFound();
        }

        foreach ($requestCollection as $requestEntity) {
            $id = $requestEntity[$this->getRepository()->getKey()];
            $model = $models->get($id);

            /** @var BaseModel $model */
            $model->fill($requestEntity);
        }

        $this->getRepository()->saveMany($models);

        return $this->getResponder()->noContent();
    }

    /**
     * Delete an entity.
     *
     * @param  string   $id
     * @return Response
     */
    public function deleteOne($id)
    {
        $model = $this->getRepository()->find($id);
        $this->getRepository()->delete($model);

        return $this->getResponder()->noContent();
    }

    /**
     * Delete many entites.
     *
     * @param  Request  $request
     * @return Response
     */
    public function deleteMany(Request $request)
    {
        $requestCollection =$request->data;
        $ids = $this->getIds($requestCollection);
        $models = $this->getRepository()->findMany($ids);
        if ($models->count() !== count($ids)) {
            $this->getResponder()->errorNotFound();
        }
        $this->getRepository()->deleteMany($models);
        return $this->getResponder()->noContent();
    }

    /**
     * @return ApiResponder
     */
    public function getResponder()
    {
        return $this->responder;
    }

    /**
     * @return BaseRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param $entityCollection
     * @param bool $exceptionOnEmpty
     * @return array
     */
    protected function getIds($entityCollection, $exceptionOnEmpty = true)
    {
        $ids = [];
        foreach ($entityCollection as $requestEntity) {
            if (isset($requestEntity[$this->getRepository()->getKey()])){
                $ids[] = $requestEntity[$this->getRepository()->getKey()];
            }
        }
        if ($exceptionOnEmpty && empty($ids)){
            $this->getResponder()->errorNotFound();
        }
        return $ids;
    }
}
