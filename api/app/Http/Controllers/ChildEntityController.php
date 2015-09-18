<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Extensions\Controller\RequestValidationTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Http\Request;
use Spira\Model\Collection\Collection;
use Spira\Model\Model\BaseModel;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Response\ApiResponse;

class ChildEntityController extends ApiController
{
    use RequestValidationTrait;

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

        if (! $this->relationName) {
            throw new \InvalidArgumentException('You must specify relationName in '.static::class);
        }

        if (! method_exists($parentModel, $this->relationName)) {
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
     * @param bool|string $childId
     * @return ApiResponse
     */
    public function getOne(Request $request, $id, $childId = false)
    {
        $model = $this->findParentEntity($id);

        //If the child id is not passed in the url, fall back to the child id being the parent id (for the case where the relationship is HasOne with primary key being foreign parent id)
        if ($childId === false) {
            $childId = $id;
        }

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

        $this->validateRequest($request->json()->all(), $this->getValidationRules());

        $childModel->fill($request->json()->all());
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
    public function putOne(Request $request, $id, $childId = false)
    {
        $parent = $this->findParentEntity($id);

        if ($childId === false) { //if the child id is not passed in the url, check the child id matches parent id, and use that as child primary key
            $this->checkEntityIdMatchesRoute($request, $id, $this->getChildModel());
            $childId = $parent->getKey();
        } else {
            $this->checkEntityIdMatchesRoute($request, $childId, $this->getChildModel());
        }

        $childModel = $this->findOrNewChildEntity($childId, $parent);

        $this->validateRequest($request->json()->all(), $this->getValidationRules());

        $childModel->fill($request->json()->all());
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

        $requestCollection = $request->json()->all();
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
     * @param  Request $request
     * @param  string $id
     * @param bool|string $childId
     * @return ApiResponse
     */
    public function patchOne(Request $request, $id, $childId = false)
    {
        $parent = $this->findParentEntity($id);

        if ($childId === false) { //if the child id is not passed in the url, check the child id matches parent id, and use that as child primary key
            $this->checkEntityIdMatchesRoute($request, $id, $this->getChildModel(), false);
            $childId = $parent->getKey();
        } else {
            $this->checkEntityIdMatchesRoute($request, $childId, $this->getChildModel(), false);
        }

        $childModel = $this->findOrFailChildEntity($childId, $parent);

        $this->validateRequest($request->json()->all(), $this->getValidationRules(), true);

        $childModel->fill($request->json()->all());
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
        $requestCollection = $request->json()->all();

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
    public function deleteOne($id, $childId = false)
    {
        $model = $this->findParentEntity($id);

        //If the child id is not passed in the url, fall back to the child id being the parent id (for the case where the relationship is HasOne with primary key being foreign parent id
        if ($childId === false) {
            $childId = $id;
        }

        $childModel = $this->findOrFailChildEntity($childId, $model);

        $childModel->delete();
        $model->fireRevisionableEvent('deleteChild', [$childModel, $this->relationName]);

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
        $requestCollection = $request->json()->all();
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
     * @param BaseModel $parentModel
     * @return HasOneOrMany|BelongsToMany|Builder
     */
    protected function getRelation(BaseModel $parentModel)
    {
        return $parentModel->{$this->relationName}();
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
}
