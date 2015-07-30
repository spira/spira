<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:37
 */

namespace App\Http\Controllers;

use App\Exceptions\ValidationException;
use App\Exceptions\ValidationExceptionCollection;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Spira\Repository\Collection\Collection;
use Spira\Repository\Model\BaseModel;
use Spira\Responder\Paginator\PaginatedRequestDecoratorInterface;
use Spira\Responder\Responder\ApiResponder;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiController extends Controller
{
    const PAGINATOR_DEFAULT_LIMIT = 10;
    const PAGINATOR_MAX_LIMIT = 50;

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

    public function getAllPaginated(PaginatedRequestDecoratorInterface $request)
    {
        $count = $this->getRepository()->count();
        $limit = $request->getLimit(static::PAGINATOR_DEFAULT_LIMIT, static::PAGINATOR_MAX_LIMIT);
        $offset = $request->isGetLast()?$count-$limit:$request->getOffset();
        //eloquent do not allow 0 or negative limit and offset
        if ($limit <= 0){
            $collection = new Collection();
        }else{
            $collection = $this->getRepository()->all(['*'],$offset,$limit);
        }
        return $this->responder->paginatedCollection($collection, $offset, $count);
    }

    /**
     * Get one entity.
     *
     * @param  string $id
     * @return Response
     */
    public function getOne($id)
    {
        $this->validateId($id);
        try {
            $model = $this->getRepository()->find($id);
            return $this->getResponder()->item($model);
        } catch (ModelNotFoundException $e) {
            $this->notFound($this->getKeyName());
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
        $this->validateId($id);
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

        $ids = $this->getIds($requestCollection, false);
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
        $this->validateId($id);
        try {
            $model = $this->getRepository()->find($id);
            $model->fill($request->all());
            $this->getRepository()->save($model);
        } catch (ModelNotFoundException $e) {
            $this->notFound($this->getKeyName());
        }

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
            $this->notFoundMany($ids, $models);
        }

        foreach ($requestCollection as $requestEntity) {
            $id = $requestEntity[$this->getKeyName()];
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
        $this->validateId($id);
        try {
            $model = $this->getRepository()->find($id);
            $this->getRepository()->delete($model);
        } catch (ModelNotFoundException $e) {
            $this->notFound($this->getKeyName());
        }

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
        $requestCollection = $request->data;
        $ids = $this->getIds($requestCollection);
        $models = $this->getRepository()->findMany($ids);

        if (count($ids) !== $models->count()) {
            $this->notFoundMany($ids, $models);
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
     * @return mixed
     */
    public function getKeyName()
    {
        return $this->getRepository()->getKeyName();
    }

    /**
     * @param $entityCollection
     * @param bool $validate
     * @return array
     * @throws ValidationExceptionCollection
     */
    protected function getIds($entityCollection, $validate = true)
    {
        $ids = [];
        $errors = [];
        $error = false;
        foreach ($entityCollection as $requestEntity) {
            if (isset($requestEntity[$this->getKeyName()]) && $requestEntity[$this->getKeyName()]) {
                try {
                    $id = $requestEntity[$this->getKeyName()];
                    $this->validateId($id);
                    $ids[] = $id;
                    $errors[] = null;
                } catch (ValidationException $e) {
                    if ($validate) {
                        $error = true;
                        $errors[] = $e;
                    }
                }
            } else {
                $errors[] = null;
            }
        }
        if ($error) {
            throw new ValidationExceptionCollection($errors);
        }

        return $ids;
    }


    protected function notFound()
    {
        $validation = $this->getValidationFactory()->make([$this->getKeyName()=>$this->getKeyName()], [$this->getKeyName()=>'notFound']);
        if ($validation->fails()) {
            throw new ValidationException($validation->getMessageBag());
        }
    }

    /**
     * @param $ids
     * @param Collection $models
     */
    protected function notFoundMany($ids, $models)
    {
        $errors = [];
        foreach ($ids as $id) {
            if ($models->get($id)) {
                $errors[] = null;
            } else {
                try {
                    $this->notFound();
                } catch (ValidationException $e) {
                    $errors[] = $e;
                }
            }
        }

        throw new ValidationExceptionCollection($errors);
    }

    /**
     * @param $id
     * @throw ValidationException
     */
    protected function validateId($id)
    {
        $validation = $this->getValidationFactory()->make([$this->getKeyName()=>$id], [$this->getKeyName()=>'uuid']);
        if ($validation->fails()) {
            throw new ValidationException($validation->getMessageBag());
        }
    }
}
