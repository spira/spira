<?php namespace App\Http\Controllers;

use App;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

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
     * @var App\Services\Validation\TestValidator
     */
    protected $validator;

    /**
     * Transformer to use for responses.
     *
     * @var string
     */
    protected $transformer = 'App\Http\Transformers\BaseTransformer';

    /**
     * Get all entities.
     *
     * @return Response
     */
    public function getAll()
    {
        return $this->collection($this->repository->all());
    }

    /**
     * Get one entity.
     *
     * @param  string  $id
     * @return Response
     */
    public function getOne($id)
    {
        return $this->item($this->repository->find($id));
    }

    /**
     * Post a new entity.
     *
     * @param  Request $request
     * @return mixed
     */
    public function postOne(Request $request)
    {
        if (!$this->validator->with($request->all())->passes()) {
            return $this->validator->errors();
        }

        return $this->item($this->repository->create($request->all()));
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
        if (!$this->validator->put()->with(array_add($request->all(), 'entity_id', $id))->passes()) {
            return $this->validator->errors();
        }

        return $this->repository->createOrReplace($id, $request->all());
    }

    /**
     * Put many entites.
     *
     * @param  Request  $request
     * @return Response
     */
    public function putMany(Request $request)
    {
        foreach ($request->data as $entity) {
            if (!$this->validator->put()->with($entity)->passes()) {
                return $this->validator->errors();
            }
        }

        return $this->repository->createOrReplaceMany($request->data);
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
        if (!$this->validator->with(array_add($request->all(), 'entity_id', $id))->patch()->passes()) {
            return $this->validator->errors();
        }

        return (string) $this->repository->update($id, $request->all());
    }

    /**
     * Patch many entites.
     *
     * @param  Request  $request
     * @return Response
     */
    public function patchMany(Request $request)
    {
        foreach ($request->data as $entity) {
            if (!$this->validator->with($entity)->patch()->passes()) {
                return $this->validator->errors();
            }
        }
        return (string) $this->repository->updateMany($request->data);
    }

    /**
     * Delete an entity.
     *
     * @param  string   $id
     * @return Response
     */
    public function deleteOne($id)
    {
        if (!$this->validator->delete()->with(['entity_id' => $id])->passes()) {
            return $this->validator->errors();
        }

        return $this->repository->delete($id);
    }

    /**
     * Delete many entites.
     *
     * @param  Request  $request
     * @return Response
     */
    public function deleteMany(Request $request)
    {
        foreach ($request->data as $id) {
            if (!$this->validator->delete()->with(['entity_id' => $id])->passes()) {
                return $this->validator->errors();
            }
        }
        return $this->repository->deleteMany($request->data);
    }

    /**
     * Transform an item for response.
     *
     * @param  Model  $item
     * @return array
     */
    protected function item(Model $item)
    {
        $transformer = App::make('App\Services\Transformer');

        return $transformer->item($item, new $this->transformer);
    }

    /**
     * Transform a collection for response.
     *
     * @param  Collection $collection
     * @return array
     */
    protected function collection(Collection $collection)
    {
        $transformer = App::make('App\Services\Transformer');

        return $transformer->collection($collection, new $this->transformer);
    }

    public static function renderException($request, \Exception $e, $debug = false){

        $message = $e->getMessage();
        if (!$message){
            $message = 'An error occurred';
        }

        $debugData = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
        ];

        $response = [
            'message' => $message,
        ];

        $statusCode = 500;

        if ($e instanceof HttpExceptionInterface){
            $statusCode = $e->getStatusCode();
        }

        if ($debug){
            $response['debug'] = $debugData;
        }

        return response()->json($response, $statusCode);
    }

}
