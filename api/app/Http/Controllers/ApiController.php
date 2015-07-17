<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:37
 */

namespace App\Http\Controllers;

use App\Helpers\RouteHelper;
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
        return $this->responder->collection($this->repository->all());
    }

    /**
     * Get one entity.
     *
     * @param  string $id
     * @return Response
     */
    public function getOne($id)
    {
        return $this->responder->item($this->repository->find($id));
    }

    /**
     * Post a new entity.
     *
     * @param  Request $request
     * @return Response
     */
    public function postOne(Request $request)
    {
        $model = $this->repository->getModel();
        $model->fill($request->all());
        $this->repository->save($model);
        $response = $this->responder->created();
        if ($route = RouteHelper::getRoute($model)){
            $response->setContent(json_encode([$route]));
        }
        return $response;
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
        try{
            $model = $this->repository->find($id);
        }catch (ModelNotFoundException $e){
            $model = $this->repository->getModel();
        }
        $model->fill($request->all());
        $this->repository->save($model);

        $response = $this->responder->created();
        if ($route = RouteHelper::getRoute($model)){
            $response->setContent(json_encode([$route]));
        }

        return $response;
    }

    /**
     * Put many entities.
     *
     * @param  Request  $request
     * @return Response
     */
    public function putMany(Request $request)
    {
        $data = $request->data;
        $idName = $this->repository->getModel()->getKeyName();
        $ids = [];
        foreach ($data as $piece)
        {
            $ids[] = $piece[$idName];
        }
        $models = $this->repository->findMany($ids);

        $putModels = [];
        foreach ($data as $piece)
        {
            $id = $piece[$idName];
            if ($models->has($id)){
                $model = $models->get($id);
            }else{
                $model = $this->repository->getModel();
            }
            /** @var BaseModel $model */
            $model->fill($piece);
            $putModels[] = $model;
        }

        $models = $this->repository->saveMany($putModels);

        $response = $this->responder->created();
        $routes = [];
        foreach ($models as $model)
        {
            if ($route = RouteHelper::getRoute($model)){
                $routes[] = $route;
            }
        }
        $response->setContent(json_encode($routes));

        return $response;
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
        $model = $this->repository->find($id);
        $model->fill($request->all());
        $this->repository->save($model);

        return $this->responder->noContent();
    }

    /**
     * Patch many entites.
     *
     * @param  Request  $request
     * @return Response
     */
    public function patchMany(Request $request)
    {
        $data = $request->data;
        $idName = $this->repository->getModel()->getKeyName();
        $ids = [];
        foreach ($data as $piece)
        {
            $ids[] = $piece[$idName];
        }
        $models = $this->repository->findMany($ids);
        if ($models->count() !== count($ids)){
            throw new \InvalidArgumentException('Not all entities were found');
        }

        foreach ($data as $piece)
        {
            $id = $piece[$idName];
            $model = $models->get($id);

            /** @var BaseModel $model */
            $model->fill($piece);
        }

        $this->repository->saveMany($models);

        return $this->responder->noContent();
    }

    /**
     * Delete an entity.
     *
     * @param  string   $id
     * @return Response
     */
    public function deleteOne($id)
    {
        $model = $this->repository->find($id);
        $this->repository->delete($model);

        return $this->responder->noContent();
    }

    /**
     * Delete many entites.
     *
     * @param  Request  $request
     * @return Response
     */
    public function deleteMany(Request $request)
    {
        $ids =$request->data;
        $models = $this->repository->findMany($ids);
        if ($models->count() !== count($ids)){
            throw new \InvalidArgumentException('Not all entities were found');
        }
        $this->repository->deleteMany($models);
        return $this->responder->noContent();
    }
}