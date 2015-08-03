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
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Paginator\PaginatedRequestDecoratorInterface;
use Spira\Responder\Response\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiController extends Controller
{
    protected $paginatorDefaultLimit = 10;
    protected $paginatorMaxLimit = 50;
    protected $validateRequest = true;

    /**
     * Model Repository.
     *
     * @var BaseRepository
     */
    protected $repository;

    /**
     * @var TransformerInterface
     */
    protected $transformer;

    /**
     * Get all entities.
     *
     * @return Response
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
     * @return Response
     */
    public function getOne($id)
    {
        if ($this->validateRequest){
            $this->validateId($id, $this->getKeyName());
        }

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
     * @return Response
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
     * @return Response
     */
    public function putOne($id, Request $request)
    {
        if ($this->validateRequest){
            $this->validateId($id, $this->getKeyName());
        }
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
     * @return Response
     */
    public function putMany(Request $request)
    {
        $requestCollection = $request->data;

        $ids = $this->getIds($requestCollection, $this->getKeyName(), $this->validateRequest);
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
     * @return Response
     */
    public function patchOne($id, Request $request)
    {
        if ($this->validateRequest){
            $this->validateId($id, $this->getKeyName());
        }
        try {
            $model = $this->getRepository()->find($id);
            $model->fill($request->all());
            $this->getRepository()->save($model);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getKeyName());
        }

        return $this->getResponse()->noContent();
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
        $ids = $this->getIds($requestCollection, $this->getKeyName(), $this->validateRequest);
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
     * @return Response
     */
    public function deleteOne($id)
    {
        if ($this->validateRequest){
            $this->validateId($id, $this->getKeyName());
        }
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
     * @return Response
     */
    public function deleteMany(Request $request)
    {
        $requestCollection = $request->data;
        $ids = $this->getIds($requestCollection, $this->getKeyName(), $this->validateRequest);
        $models = $this->getRepository()->findMany($ids);

        if (count($ids) !== $models->count()) {
            throw $this->notFoundManyException($ids, $models, $this->getKeyName());
        }

        $this->getRepository()->deleteMany($models);
        return $this->getResponse()->noContent();
    }

    /**
     * @return ApiResponse
     */
    public function getResponse()
    {
        return new ApiResponse();
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
     * @param string $keyName
     * @param bool $validate
     * @param string $rule
     * @return array
     * @throws ValidationExceptionCollection
     */
    protected function getIds($entityCollection, $keyName, $validate = true, $rule = 'uuid')
    {
        $ids = [];
        $errors = [];
        $error = false;
        foreach ($entityCollection as $requestEntity) {
            if (isset($requestEntity[$keyName]) && $requestEntity[$keyName]) {
                try {
                    $id = $requestEntity[$keyName];
                    if ($validate) {
                        $this->validateId($id, $keyName, $rule);
                    }
                    $ids[] = $id;
                    $errors[] = null;
                } catch (ValidationException $e) {
                    $error = true;
                    $errors[] = $e;
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


    /**
     * Build notFoundException
     * @param string $keyName
     * @return ValidationException
     */
    protected function notFoundException($keyName = '')
    {
        $validation = $this->getValidationFactory()->make([$keyName=>$keyName], [$keyName=>'notFound']);
        if (!$validation->fails()) {
            // @codeCoverageIgnoreStart
            throw new \LogicException("Validator should have failed");
            // @codeCoverageIgnoreEnd
        }

        return new ValidationException($validation->getMessageBag());
    }

    /**
     * Get notFoundManyException
     * @param $ids
     * @param Collection $models
     * @param string $keyName
     * @return ValidationExceptionCollection
     */
    protected function notFoundManyException($ids, $models, $keyName = '')
    {
        $errors = [];
        foreach ($ids as $id) {
            if ($models->get($id)) {
                $errors[] = null;
            } else {
                try {
                    throw $this->notFoundException($keyName);
                } catch (ValidationException $e) {
                    $errors[] = $e;
                }
            }
        }

        return new ValidationExceptionCollection($errors);
    }

    /**
     * @param $id
     * @param string $keyName
     * @param string $rule
     * @throw ValidationException
     */
    protected function validateId($id, $keyName, $rule = 'uuid')
    {
        $validation = $this->getValidationFactory()->make([$keyName=>$id], [$keyName=>$rule]);
        if ($validation->fails()) {
            throw new ValidationException($validation->getMessageBag());
        }
    }
}
