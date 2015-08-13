<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 04.08.15
 * Time: 20:23
 */

namespace App\Http\Controllers;

use App\Extensions\Controller\RequestValidationTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Http\Request;
use Spira\Repository\Collection\Collection;
use Spira\Repository\Model\BaseModel;
use Spira\Repository\Validation\ValidationException;
use Spira\Repository\Validation\ValidationExceptionCollection;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Response\ApiResponse;

class ChildEntityController extends ApiController
{
    use RequestValidationTrait;

    protected $validateParentIdRule = null;
    protected $validateChildIdRule = null;
    protected $relationName = null;

    /**
     * @var BaseModel
     */
    protected $cacheChildModel;

    /**
     * @var BaseModel
     */
    protected $parentModel;

    public function __construct(BaseModel $parentModel, TransformerInterface $transformer)
    {
        $this->parentModel = $parentModel;

        if (!$this->relationName) {
            throw new \InvalidArgumentException('You should specify relationName in '.static::class);
        }

        if (!method_exists($parentModel, $this->relationName)) {
            throw new \InvalidArgumentException('Relation '.$this->relationName.', acquired by '.
                static::class.', does not exist in '.get_class($parentModel)
            );
        }
        parent::__construct($transformer);
    }


    /**
     * Get all entities.
     *
     * @param string $id
     * @return ApiResponse
     */
    public function getAll($id)
    {
        $model = $this->findParentEntity($id);
        $childEntities = $this->findAllChildren($model);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->collection($childEntities);
    }

    /**
     * Get one entity.
     *
     * @param  string $id
     * @param string $childId
     * @return ApiResponse
     */
    public function getOne($id, $childId)
    {
        $model = $this->findParentEntity($id);
        $childModel = $this->findOrFailChildEntity($childId, $model);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($childModel);
    }

    /**
     * Post a new entity.
     *
     * @param string $id
     * @param  Request $request
     * @return ApiResponse
     * @throws \Exception
     * @throws \Exception|null
     * @throws \Spira\Repository\Repository\RepositoryException
     */
    public function postOne($id, Request $request)
    {
        $model = $this->findParentEntity($id);
        $childModel = $this->getChildModel()->newInstance();
        $this->validateRequest($request->all(),$this->getValidationRules());
        $childModel->fill($request->all());
        $this->getRelation($model)->save($childModel);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdItem($childModel);
    }

    /**
     * Put an entity.
     *
     * @param  string $id
     * @param string $childId
     * @param  Request $request
     * @return ApiResponse
     */
    public function putOne($id, $childId, Request $request)
    {
        $model = $this->findParentEntity($id);
        $childModel = $this->findOrNewChildEntity($childId, $model);
        $validationRules = $this->getValidationRules();
        if ($childModel->exists){
            $validationRules = $this->addIdOverrideValidationRule($validationRules,$childId);
        }
        $this->validateRequest($request->all(),$validationRules, $childModel->exists);
        $childModel->fill($request->all());
        $this->getRelation($model)->save($childModel);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdItem($childModel);
    }

    /**
     * Put many entities.
     *
     * @param string $id
     * @param  Request $request
     * @return ApiResponse
     */
    public function putMany($id, Request $request)
    {
        $requestCollection = $request->data;
        $model = $this->findParentEntity($id);
        $childModels = $this->findChildrenCollection($requestCollection,$model);

        $error = false;
        $errors = [];
        foreach ($requestCollection as $requestEntity) {
            $id = $this->getIdOrNull($requestEntity, $this->getChildModel()->getKeyName());
            if ($id && !empty($childModels) && $childModels->has($id)) {
                $childModel = $childModels->get($id);
            } else {
                $childModel = $this->getChildModel()->newInstance();
                $childModels->add($childModel);
            }

            try {
                $this->validateRequest($request->all(),$this->getValidationRules(), $model->exists);
                if (!$error){
                    $childModel->fill($requestEntity);
                }
                $errors[] = null;
            } catch (ValidationException $e) {
                $error = true;
                $errors[] = $e;
            }
        }

        if ($error){
            throw new ValidationExceptionCollection($errors);
        }

        $this->getRelation($model)->saveMany($childModels);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdCollection($childModels);
    }

    /**
     * Patch an entity.
     *
     * @param  string $id
     * @param string $childId
     * @param  Request $request
     * @return ApiResponse
     */
    public function patchOne($id, $childId, Request $request)
    {
        $model = $this->findParentEntity($id);
        $childModel = $this->findOrFailChildEntity($childId, $model);
        $validationRules = $this->addIdOverrideValidationRule($this->getValidationRules(),$childId);
        $this->validateRequest($request->all(),$validationRules, true);
        $childModel->fill($request->all());
        $this->getRelation($model)->save($childModel);

        return $this->getResponse()->noContent();
    }

    /**
     * Patch many entites.
     *
     * @param string $id
     * @param  Request $request
     * @return ApiResponse
     */
    public function patchMany($id, Request $request)
    {
        $requestCollection = $request->data;
        $model = $this->findParentEntity($id);
        $childModels = $this->findOrFailChildrenCollection($requestCollection, $model);

        $error = false;
        $errors = [];
        foreach ($requestCollection as $requestEntity) {
            $id = $this->getIdOrNull($requestEntity, $this->getChildModel()->getKeyName());
            $childModel = $childModels->get($id);

            try {
                $this->validateRequest($request->all(),$this->getValidationRules(), true);
                if (!$error){
                    $childModel->fill($requestEntity);
                }
                $errors[] = null;
            } catch (ValidationException $e) {
                $error = true;
                $errors[] = $e;
            }
        }

        if ($error){
            throw new ValidationExceptionCollection($errors);
        }

        $this->getRelation($model)->saveMany($childModels);

        return $this->getResponse()->noContent();
    }

    /**
     * Delete an entity.
     *
     * @param  string $id
     * @param string $childId
     * @return ApiResponse
     */
    public function deleteOne($id, $childId)
    {
        $model = $this->findParentEntity($id);
        $childModel = $this->findOrFailChildEntity($childId, $model);
        $childModel->delete();

        return $this->getResponse()->noContent();
    }

    /**
     * Delete many entites.
     *
     * @param string $id
     * @param  Request  $request
     * @return ApiResponse
     */
    public function deleteMany($id, Request $request)
    {
        $requestCollection = $request->data;
        $model = $this->findParentEntity($id);
        $childModels = $this->findOrFailChildrenCollection($requestCollection, $model);
        foreach ($childModels as $childModel) {
            $childModel->delete();
        }

        return $this->getResponse()->noContent();
    }

    /**
     * @return BaseModel
     */
    public function getParentModel()
    {
        return $this->parentModel;
    }

    /**
     * @return BaseModel
     */
    public function getChildModel()
    {
        if (is_null($this->cacheChildModel)){
            $this->cacheChildModel = $this->getRelation($this->parentModel)->getRelated();
        }
        return $this->cacheChildModel;
    }

    /**
     * @param BaseModel $model
     * @return HasOneOrMany|Builder
     */
    protected function getRelation(BaseModel $model)
    {
        return $model->{$this->relationName}();
    }


    /**
     * @param $id
     * @return BaseModel
     */
    protected function findParentEntity($id)
    {
        $this->validateId($id, $this->getParentModel()->getKeyName(), $this->getValidateParentIdRule());
        try {
            return $this->getParentModel()->findByIdentifier($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }
    }

    /**
     * @param $id
     * @param BaseModel $parent
     * @return BaseModel
     */
    protected function findOrNewChildEntity($id, BaseModel $parent)
    {
        $this->validateId($id, $this->getChildModel()->getKeyName(), $this->getValidateChildIdRule());

        try {
            return $this->getRelation($parent)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $this->getChildModel()->newInstance();
        }
    }

    /**
     * @param $id
     * @param BaseModel $parent
     * @return BaseModel
     */
    protected function findOrFailChildEntity($id, BaseModel $parent)
    {
        $this->validateId($id, $this->getChildModel()->getKeyName(), $this->getValidateChildIdRule());

        try {
            return $this->getRelation($parent)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getChildModel()->getKeyName());
        }
    }

    /**
     * @param $parent
     * @return Collection
     */
    protected function findAllChildren($parent)
    {
        return $this->getRelation($parent)->getResults();
    }

    /**
     * @param $requestCollection
     * @param BaseModel $parent
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function findOrFailChildrenCollection($requestCollection, BaseModel $parent)
    {
        $ids = $this->getIds($requestCollection, $this->getChildModel()->getKeyName(), $this->getValidateChildIdRule());

        if (!empty($ids)) {
            $models = $this->getRelation($parent)->findMany($ids);
        }else{
            $models = $this->getChildModel()->newCollection();
        }

        if ($models && count($ids) !== $models->count()) {
            throw $this->notFoundManyException($ids, $models, $this->getChildModel()->getKeyName());
        }

        return $models;
    }

    protected function findChildrenCollection($requestCollection, BaseModel $parent)
    {
        $ids = $this->getIds($requestCollection, $this->getChildModel()->getKeyName(), $this->getValidateChildIdRule());

        if (!empty($ids)) {
            $models = $this->getRelation($parent)->findMany($ids);
        }else{
            $models = $this->getChildModel()->newCollection();
        }

        return $models;
    }

    /**
     * Get id validation rule from child model validation rules
     * Can be overriden by validateChildIdRule property
     * @return null|string
     */
    protected function getValidateChildIdRule()
    {
        if ($this->validateChildIdRule){
            return $this->validateChildIdRule;
        }

        if (isset($this->getValidationRules()[$this->getChildModel()->getKeyName()])){
            return $this->getValidationRules()[$this->getChildModel()->getKeyName()];
        }

        return null;
    }

    /**
     * Get id validation rule from parent model validation rules
     * Can be overriden by validateParentIdRule property
     * @return null|string
     */
    protected function getValidateParentIdRule()
    {
        if ($this->validateParentIdRule){
            return $this->validateParentIdRule;
        }

        if (isset($this->getParentModel()->getValidationRules()[$this->getParentModel()->getKeyName()])){
            return $this->getParentModel()->getValidationRules()[$this->getParentModel()->getKeyName()];
        }

        return null;
    }

    protected function getValidationRules()
    {
        return $this->getChildModel()->getValidationRules();
    }

    protected function addIdOverrideValidationRule($validationRules, $id)
    {
        $rule = 'equals:'.$id;
        $keyName = $this->getChildModel()->getKeyName();
        if (isset($validationRules[$keyName])){
            $rule='|'.$rule;
        }

        $validationRules[$keyName].= $rule;
        return $validationRules;
    }
}
