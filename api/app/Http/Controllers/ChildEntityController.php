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
use Spira\Model\Collection\Collection;
use Spira\Model\Model\BaseModel;
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
            throw new \InvalidArgumentException('You must specify relationName in '.static::class);
        }

        if (!method_exists($parentModel, $this->relationName)) {
            throw new \InvalidArgumentException('Relation '.$this->relationName.', required by '.
                static::class.', does not exist in '.get_class($parentModel)
            );
        }
        parent::__construct($transformer);
    }


    /**
     * Get all entities.
     *
     * @param Request $request
     * @param string $id
     * @return ApiResponse
     */
    public function getAll(Request $request, $id)
    {
        $model = $this->findParentEntity($id);
        $childEntities = $this->findAllChildren($model);
        $childEntities = $this->getWithNested($childEntities, $request);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($childEntities);
    }

    /**
     * Get one entity.
     *
     * @param Request $request
     * @param  string $id
     * @param string $childId
     * @return ApiResponse
     */
    public function getOne(Request $request, $id, $childId)
    {
        $model = $this->findParentEntity($id);
        $childModel = $this->findOrFailChildEntity($childId, $model);
        $childModel = $this->getWithNested($childModel, $request);

        return $this->getResponse()
            ->transformer($this->getTransformer())
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
     */
    public function postOne(Request $request, $id)
    {
        $parent = $this->findParentEntity($id);
        $childModel = $this->getChildModel()->newInstance();

        $this->validateRequest($request->all(), $this->getValidationRules());

        $childModel->fill($request->all());
        $this->getRelation($parent)->save($childModel);

        return $this->getResponse()
            ->transformer($this->getTransformer())
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
    public function putOne(Request $request, $id, $childId)
    {
        $parent = $this->findParentEntity($id);

        $this->checkEntityIdMatchesRoute($request, $childId, $this->getChildModel());
        $childModel = $this->findOrNewChildEntity($childId, $parent);

        $this->validateRequest($request->all(), $this->addIdOverrideValidationRule($this->getValidationRules(), $childId));

        $childModel->fill($request->all());
        $this->getRelation($parent)->save($childModel);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->createdItem($childModel);
    }

    /**
     * Put many entities.
     *
     * @param string $id
     * @param  Request $request
     * @return ApiResponse
     */
    public function putMany(Request $request, $id)
    {
        $parent = $this->findParentEntity($id);

        $requestCollection = $request->data;
        $this->validateRequestCollection($requestCollection, $this->getValidationRules());

        $existingChildModels = $this->findChildrenCollection($requestCollection, $parent);

        $childModels = $this->getChildModel()
            ->hydrateRequestCollection($requestCollection, $existingChildModels);

        $this->getRelation($parent)->saveMany($childModels);

        return $this->getResponse()
            ->transformer($this->getTransformer())
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
    public function patchOne(Request $request, $id, $childId)
    {
        $parent = $this->findParentEntity($id);

        $this->checkEntityIdMatchesRoute($request, $childId, $this->getChildModel(), false);
        $childModel = $this->findOrFailChildEntity($childId, $parent);

        $validationRules = $this->addIdOverrideValidationRule($this->getValidationRules(), $childId);
        $this->validateRequest($request->all(), $validationRules, true);

        $childModel->fill($request->all());
        $this->getRelation($parent)->save($childModel);

        return $this->getResponse()->noContent();
    }

    /**
     * Patch many entites.
     *
     * @param string $id
     * @param  Request $request
     * @return ApiResponse
     */
    public function patchMany(Request $request, $id)
    {
        $requestCollection = $request->data;
        $this->validateRequestCollection($requestCollection, $this->getValidationRules(), true);

        $parent = $this->findParentEntity($id);
        $existingChildModels = $this->findOrFailChildrenCollection($requestCollection, $parent);

        $childModels = $this->getChildModel()
            ->hydrateRequestCollection($requestCollection, $existingChildModels);

        $this->getRelation($parent)->saveMany($childModels);

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
    public function deleteMany(Request $request, $id)
    {
        $requestCollection = $request->data;
        $model = $this->findParentEntity($id);

        $this->findOrFailChildrenCollection($requestCollection, $model)->each(function (BaseModel $model) {
            $model->delete();
        });

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
        if (is_null($this->cacheChildModel)) {
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
        $ids = $this->getIds($requestCollection, $this->getChildModel()->getKeyName());

        if (empty($ids)) {
            $models = $this->getChildModel()->newCollection();
            throw $this->notFoundManyException($ids, $models, $this->getChildModel()->getKeyName());
        }

        $models = $this->getRelation($parent)->findMany($ids);

        if ($models && count($ids) !== $models->count()) {
            throw $this->notFoundManyException($ids, $models, $this->getChildModel()->getKeyName());
        }

        return $models;
    }

    /**
     * @param $requestCollection
     * @param BaseModel $parent
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function findChildrenCollection($requestCollection, BaseModel $parent)
    {
        $ids = $this->getIds($requestCollection, $this->getChildModel()->getKeyName());

        $models = $this->getRelation($parent)->findMany($ids);

        return $models;
    }

    /**
     * @return array
     */
    protected function getValidationRules()
    {
        return $this->getChildModel()->getValidationRules();
    }

    /**
     * @param $validationRules
     * @param $id
     * @return mixed
     */
    protected function addIdOverrideValidationRule($validationRules, $id)
    {
        $rule = 'equals:'.$id;
        $keyName = $this->getChildModel()->getKeyName();
        if (isset($validationRules[$keyName])) {
            $rule='|'.$rule;
        }

        $validationRules[$keyName].= $rule;
        return $validationRules;
    }
}
