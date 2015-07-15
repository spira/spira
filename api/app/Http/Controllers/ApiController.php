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
        return $this->responder->created();
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
        return $this->responder->created();
    }

    /**
     * Put many entities.
     *
     * @param  Request  $request
     * @return Response
     */
    public function putMany(Request $request)
    {
        return $this->responder->created();
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
        //$this->repository->updateMany($request->data);
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
        $this->repository->deleteMany($request->data);
        return $this->responder->noContent();
    }
}