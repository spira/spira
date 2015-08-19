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
use Spira\Model\Collection\Collection;
use Spira\Model\Model\BaseModel;
use Spira\Model\Validation\ValidationException;
use Spira\Model\Validation\ValidationExceptionCollection;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Paginator\PaginatedRequestDecoratorInterface;
use Spira\Responder\Response\ApiResponse;

abstract class EntityController extends ApiController
{
    use RequestValidationTrait;

    protected $validateIdRule = null;

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
            ->transformer($this->getTransformer())
            ->collection($this->getAllEntities());
    }

    public function getAllPaginated(PaginatedRequestDecoratorInterface $request)
    {
        $count = $this->countEntities();
        $limit = $request->getLimit($this->paginatorDefaultLimit, $this->paginatorMaxLimit);
        $offset = $request->isGetLast()?$count-$limit:$request->getOffset();
        $collection = $this->getAllEntities($limit, $offset);

        return $this->getResponse()
            ->transformer($this->getTransformer())
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
        $model = $this->findOrFailEntity($id);
        return $this->getResponse()
            ->transformer($this->getTransformer())
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
        $this->validateRequest($request->all(), $this->getValidationRules());
        $model->fill($request->all());
        $model->save();

        return $this->getResponse()
            ->transformer($this->getTransformer())
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
        $model = $this->findOrNewEntity($id);
        $this->validateRequest($request->all(), $model->getValidationRules());

        $model->fill($request->all())
            ->save();

        return $this->getResponse()
            ->transformer($this->getTransformer())
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

        $this->validateRequestCollection($requestCollection, $this->getValidationRules());

        $modelCollection = $this->findCollection($requestCollection);

        foreach ($requestCollection as $requestEntity) {
            $id = $this->getIdOrNull($requestEntity, $this->getModel()->getKeyName());

            $model = $modelCollection->get($id, false);

            if (!$model){
                $model = $this->getModel()->newInstance();
                $modelCollection->add($model);
            }

            $model->fill($requestEntity)->save();
        }

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->createdCollection($modelCollection);
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
        $model = $this->findOrFailEntity($id);
        $validationRules = $this->addIdOverrideValidationRule($this->getValidationRules(), $id);
        $this->validateRequest($request->all(), $validationRules, true);
        $model->fill($request->all());
        $model->save();

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
        $models = $this->findOrFailCollection($requestCollection);

        $error = false;
        $errors = [];
        foreach ($requestCollection as $requestEntity) {
            $id = $requestEntity[$this->getModel()->getKeyName()];
            $model = $models->get($id);

            try {
                $this->validateRequest($requestEntity, $this->getValidationRules(), true);
                if (!$error) {
                    $model->fill($requestEntity);
                    $model->save();
                }
                $errors[] = null;
            } catch (ValidationException $e) {
                $error = true;
                $errors[] = $e;
            }
        }

        if ($error) {
            throw new ValidationExceptionCollection($errors);
        }

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
        $this->findOrFailEntity($id)->delete();
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
        $models = $this->findOrFailCollection($requestCollection);
        foreach ($models as $model) {
            $model->delete();
        }
        return $this->getResponse()->noContent();
    }

    /**
     * @param $id
     * @return BaseModel
     */
    protected function findOrNewEntity($id)
    {
        $this->validateId($id, $this->getModel()->getKeyName(), $this->getIdValidationRule());

        try {
            return $this->getModel()->findByIdentifier($id);
        } catch (ModelNotFoundException $e) {
            return $this->getModel()->newInstance();
        }
    }

    /**
     * @param $id
     * @return BaseModel
     */
    protected function findOrFailEntity($id)
    {
        $this->validateId($id, $this->getModel()->getKeyName(), $this->getIdValidationRule());

        try {
            return $this->getModel()->findByIdentifier($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getModel()->getKeyName());
        }
    }

    /**
     * @return int
     */
    protected function countEntities()
    {
        return $this->getModel()->count();
    }

    /**
     * @param null $limit
     * @param null $offset
     * @return Collection
     */
    protected function getAllEntities($limit = null, $offset = null)
    {
        return $this->getModel()->take($limit)->skip($offset)->get();
    }

    /**
     * @param $requestCollection
     * @return Collection
     */
    protected function findOrFailCollection($requestCollection)
    {
        $ids = $this->getIds($requestCollection, $this->getModel()->getKeyName(), $this->getIdValidationRule());

        if (!empty($ids)) {
            $models = $models = $this->getModel()->findMany($ids);
        } else {
            throw $this->notFoundManyException($ids, $this->getModel()->newCollection(), $this->getModel()->getKeyName());
        }

        if ($models && count($ids) !== $models->count()) {
            throw $this->notFoundManyException($ids, $models, $this->getModel()->getKeyName());
        }

        return $models;
    }

    /**
     * @param $requestCollection
     * @return Collection
     */
    protected function findCollection($requestCollection)
    {
        $ids = $this->getIds($requestCollection, $this->getModel()->getKeyName(), $this->getIdValidationRule());

        return $this->getModel()->findMany($ids); //if $ids is empty, findMany returns an empty collection
    }

    /**
     * @return BaseModel
     */
    protected function getModel()
    {
        return $this->model;
    }

    /**
     * Get id validation rule from model validation rules
     * Can be overriden by validateIdRule property
     * @return null|string
     */
    protected function getIdValidationRule()
    {
        if ($this->validateIdRule) {
            return $this->validateIdRule;
        }

        $validationRules = $this->getValidationRules();

        if (isset($validationRules[$this->getModel()->getKeyName()])) {
            return $validationRules[$this->getModel()->getKeyName()];
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getValidationRules()
    {
        return $this->getModel()->getValidationRules();
    }

    /**
     * @param $validationRules
     * @param $id
     * @return mixed
     */
    protected function addIdOverrideValidationRule($validationRules, $id)
    {
        $rule = 'equals:'.$id;
        $keyName = $this->getModel()->getKeyName();
        if (isset($validationRules[$keyName])) {
            $rule='|'.$rule;
        }

        $validationRules[$keyName].= $rule;
        return $validationRules;
    }
}
