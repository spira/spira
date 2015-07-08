<?php namespace App\Http\Controllers;

use App;
use App\Http\Transformers\CollectionTransformerInterface;
use App\Http\Transformers\ItemTransformerInterface;
use App\Services\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

abstract class BaseController extends Controller
{
    public static $model;

    /**
     * Model Repository.
     *
     * @var App\Repositories\BaseRepository
     */
    protected $repository;

    /**
     * Validation service.
     *
     * @var Validator
     */
    protected $validator;


    /**
     * Get all entities.
     *
     * @param CollectionTransformerInterface $transformer
     * @return Response
     */
    public function getAll(CollectionTransformerInterface $transformer)
    {
        return response($transformer->transformCollection($this->repository->all()));
    }

    /**
     * Get one entity.
     *
     * @param ItemTransformerInterface $transformer
     * @param  string $id
     * @return Response
     */
    public function getOne(ItemTransformerInterface $transformer,$id)
    {
        return response($transformer->transformItem($this->repository->find($id)));
    }

    /**
     * Post a new entity.
     *
     * @param  Request $request
     * @return Response
     */
    public function postOne(Request $request)
    {
        $this->validator->with($request->all())->validate();
        $model = $this->repository->getModel();
        $model->fill($request->all());
        return response((bool)$this->repository->save($model), 201);
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
        $this->validator->with($request->all())->id($id)->validate();
        try{
            $model = $this->repository->find($id);
        }catch (ModelNotFoundException $e){
            $model = $this->repository->getModel();
        }
        $model->fill($request->all());
        return response((bool)$this->repository->save($model), 201);
    }

    /**
     * Put many entities.
     *
     * @param  Request  $request
     * @return Response
     */
    public function putMany(Request $request)
    {
        $this->validator->with($request->data)->validateMany();

        return response($this->repository->createOrReplaceMany($request->data), 201);
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
        $this->validator->with($request->all())->id($id)->validate();

        $model = $this->repository->find($id);
        $model->fill($request->all());
        $this->repository->save($model);

        return response(null, 204);
    }

    /**
     * Patch many entites.
     *
     * @param  Request  $request
     * @return Response
     */
    public function patchMany(Request $request)
    {
        $this->validator->with($request->data)->validateMany();

        $this->repository->updateMany($request->data);

        return response(null, 204);
    }

    /**
     * Delete an entity.
     *
     * @param  string   $id
     * @return Response
     */
    public function deleteOne($id)
    {
        $this->validator->id($id)->validate();
        $model = $this->repository->find($id);
        $this->repository->delete($model);

        return response(null, 204);
    }

    /**
     * Delete many entites.
     *
     * @param  Request  $request
     * @return Response
     */
    public function deleteMany(Request $request)
    {
        $this->validator->with($request->data)->validateMany();

        $this->repository->deleteMany($request->data);

        return response(null, 204);
    }
}
