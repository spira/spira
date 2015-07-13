<?php

namespace App\Http\Controllers;

use App;
use App\Exceptions\ForbiddenException;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
     * Permission Lock Manager.
     *
     * @var Manager
     */
    protected $lock;

    /**
     * JWT Auth.
     *
     * @var Tymon\JWTAuth\JWTAuth
     */
    protected $jwtAuth;

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
     * @param string $id
     *
     * @return Response
     */
    public function getOne($id)
    {
        return $this->item($this->repository->find($id));
    }

    /**
     * Post a new entity.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postOne(Request $request)
    {
        $this->validator->with($request->all())->validate();

        return response($this->repository->create($request->all()), 201);
    }

    /**
     * Put an entity.
     *
     * @param string  $id
     * @param Request $request
     *
     * @return Response
     */
    public function putOne($id, Request $request)
    {
        $this->validator->with($request->all())->id($id)->validate();

        $result = $this->repository->createOrReplace($id, $request->all());

        return response(null, $result['code']);
    }

    /**
     * Put many entites.
     *
     * @param Request $request
     *
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
     * @param string  $id
     * @param Request $request
     *
     * @return Response
     */
    public function patchOne($id, Request $request)
    {
        $this->validator->with($request->all())->id($id)->validate();

        $this->repository->update($id, $request->all());

        return response(null, 204);
    }

    /**
     * Patch many entites.
     *
     * @param Request $request
     *
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
     * @param string $id
     *
     * @return Response
     */
    public function deleteOne($id)
    {
        $this->validator->id($id)->validate();

        $this->repository->delete($id);

        return response(null, 204);
    }

    /**
     * Delete many entites.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deleteMany(Request $request)
    {
        $this->validator->with($request->data)->validateMany();

        $this->repository->deleteMany($request->data);

        return response(null, 204);
    }

    /**
     * Transform an item for response.
     *
     * @param Model $item
     *
     * @return array
     */
    protected function item(Model $item)
    {
        $transformer = App::make('App\Services\Transformer');

        return $transformer->item($item, new $this->transformer());
    }

    /**
     * Transform a collection for response.
     *
     * @param Collection $collection
     *
     * @return array
     */
    protected function collection(Collection $collection)
    {
        $transformer = App::make('App\Services\Transformer');

        return $transformer->collection($collection, new $this->transformer());
    }

    /**
     * Check if the user has a certain permission.
     *
     * @throws ForbiddenException
     * @param  string  $action
     * @param  string  $resource
     * @return void
     */
    protected function checkPermission($action, $resource)
    {
        $user = $this->jwtAuth->getUser();
        $lock = $this->lock->makeCallerLockAware($user);

        if (!$user->can($action, $resource)) {
            throw new ForbiddenException;
        }
    }

    public static function renderException($request, \Exception $e, $debug = false)
    {
        $message = $e->getMessage();
        if (!$message) {
            $message = 'An error occurred';
        }

        $debugData = [
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'code'      => $e->getCode(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => explode("\n", $e->getTraceAsString()),
        ];

        $response = [
            'message' => $message,
        ];

        $statusCode = 500;

        if ($e instanceof HttpExceptionInterface) {
            $statusCode = $e->getStatusCode();

            if (method_exists($e, 'getResponse')) {
                $response = $e->getResponse();
            }
        }

        if ($debug) {
            $response['debug'] = $debugData;
        }

        return response()->json($response, $statusCode, [], JSON_PRETTY_PRINT);
    }
}
