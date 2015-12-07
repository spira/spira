<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\Controllers;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Spira\Core\Model\Collection\Collection;
use Spira\Core\Model\Model\BaseModel;
use Spira\Core\Responder\Response\ApiResponse;

abstract class ChildEntityController extends AbstractRelatedEntityController
{
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
        $this->checkPermission(static::class.'@getAll', ['model' => $model, 'children' => $childEntities]);

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
        $parent = $this->findParentEntity($id);

        //If the child id is not passed in the url, fall back to the child id being the parent id (for the case where the relationship is HasOne with primary key being foreign parent id)
        if ($this->childIdCanFallbackToParent($childId, $parent)) {
            $childId = $parent->getKey();
        }

        $childModel = $this->findOrFailChildEntity($childId, $parent);
        $childModel = $this->getWithNested($childModel, $request);

        $this->checkPermission(static::class.'@getOne', ['model' => $parent, 'children' => $childModel]);

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

        $this->validateRequest($request->json()->all(), $this->getValidationRules($id));

        $childModel->fill($request->json()->all());

        $this->checkPermission(static::class.'@postOne', ['model' => $parent, 'children' => $childModel]);

        $this->getRelation($parent)->save($childModel);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->created()
            ->item($childModel);
    }

    /**
     * Add many entities.
     * Internally make use of Relation::saveMany().
     *
     * @param string $id
     * @param  Request $request
     * @return ApiResponse
     */
    public function postMany(Request $request, $id)
    {
        $parent = $this->findParentEntity($id);

        $requestCollection = $request->json()->all();
        $this->validateRequestCollection($requestCollection, $this->getChildModel());

        $existingChildModels = $this->findChildrenCollection($requestCollection, $parent);

        $childModels = $this->getChildModel()
            ->hydrateRequestCollection($requestCollection, $existingChildModels);

        $this->checkPermission(static::class.'@postMany', ['model' => $parent, 'children' => $childModels]);

        $this->getRelation($parent)->saveMany($childModels);

        // @Todo: Ran into an issue where updating a child entity through "putMany" request ("hasMany"/"belongsTo" relationship) does not fire the parent's "updated" event (which means that the parent object isn't reindexed in elastic search so it will not contain the new information). "putMany" does appear to update the parent's updated timestamp which does suggest that it is touched in some way (confirmed that Laravel is issuing the command to update the time stamp of the parent). Manually touching the parent fixes this problem.
        $parent->touch();

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->created()
            ->collection($childModels);
    }

    /**
     * Put an entity.
     *
     * @param  Request $request
     * @param  string $id
     * @param bool|string $childId
     * @return ApiResponse
     */
    public function putOne(Request $request, $id, $childId = false)
    {
        $parent = $this->findParentEntity($id);

        //If the child id is not passed in the url, fall back to the child id being the parent id (for the case where the relationship is HasOne with primary key being foreign parent id)
        if ($this->childIdCanFallbackToParent($childId, $parent)) {
            $this->checkEntityIdMatchesRoute($request, $id, $this->getChildModel());
            $childId = $parent->getKey();
        } else {
            $this->checkEntityIdMatchesRoute($request, $childId, $this->getChildModel());
        }

        $childModel = $this->findOrNewChildEntity($childId, $parent);

        $this->validateRequest($request->json()->all(), $this->getValidationRules($childId));

        $childModel->fill($request->json()->all());

        $this->checkPermission(static::class.'@putOne', ['model' => $parent, 'children' => $childModel]);

        $this->getRelation($parent)->save($childModel);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->created()
            ->item($childModel);
    }

    /**
     * Put many entities.
     * Internally make use of Relation::sync().
     *
     * @param  Request $request
     * @param string $id
     * @return ApiResponse
     */
    public function putMany(Request $request, $id)
    {
        $parent = $this->findParentEntity($id);

        $requestCollection = $request->json()->all();
        $this->validateRequestCollection($requestCollection, $this->getChildModel());

        $existingChildModels = $this->findChildrenCollection($requestCollection, $parent);

        $childModels = $this->getChildModel()->hydrateRequestCollection($requestCollection, $existingChildModels);

        $this->checkPermission(static::class.'@putMany', ['model' => $parent, 'children' => $childModels]);

        $relation = $this->getRelation($parent);

        if ($relation instanceof BelongsToMany) {
            $this->saveNewItemsInCollection($childModels);
            $relation->sync($this->makeSyncList($childModels, $requestCollection));
        } else {
            $relation->saveMany($childModels);
        }

        $this->postSync($parent, $childModels);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->created()
            ->collection($childModels);
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

        //If the child id is not passed in the url, fall back to the child id being the parent id (for the case where the relationship is HasOne with primary key being foreign parent id)
        if ($this->childIdCanFallbackToParent($childId, $parent)) {
            $childId = $parent->getKey();
        } else {
            $this->checkEntityIdMatchesRoute($request, $childId, $this->getChildModel(), false);
        }

        $childModel = $this->findOrFailChildEntity($childId, $parent);

        $this->validateRequest($request->json()->all(), $this->getValidationRules($id), $childModel);

        $childModel->fill($request->json()->all());

        $this->checkPermission(static::class.'@patchOne', ['model' => $parent, 'children' => $childModel]);

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

        $this->validateRequestCollection($requestCollection, $this->getChildModel(), true);

        $parent = $this->findParentEntity($id);
        $existingChildModels = $this->findOrFailChildrenCollection($requestCollection, $parent);

        $childModels = $this->getChildModel()->hydrateRequestCollection($requestCollection, $existingChildModels);

        $this->checkPermission(static::class.'@patchMany', ['model' => $parent, 'children' => $childModels]);

        $this->getRelation($parent)->saveMany($childModels);

        return $this->getResponse()->noContent();
    }

    /**
     * Delete an entity.
     *
     * @param  string $id
     * @param bool|string $childId
     * @return ApiResponse
     * @throws \Exception
     */
    public function deleteOne($id, $childId = false)
    {
        $parent = $this->findParentEntity($id);

        //If the child id is not passed in the url, fall back to the child id being the parent id (for the case where the relationship is HasOne with primary key being foreign parent id)
        if ($this->childIdCanFallbackToParent($childId, $parent)) {
            $childId = $parent->getKey();
        }

        $childModel = $this->findOrFailChildEntity($childId, $parent);

        $this->checkPermission(static::class.'@deleteOne', ['model' => $parent, 'children' => $childModel]);

        $childModel->delete();
        $parent->fireRevisionableEvent('deleteChild', [$childModel, $this->relationName]);

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

        $childModels = $this->findOrFailChildrenCollection($requestCollection, $model);

        $this->checkPermission(static::class.'@deleteMany', ['model' => $model, 'children' => $childModels]);

        $childModels->each(function (BaseModel $model) {
            $model->delete();
        });

        return $this->getResponse()->noContent();
    }

    /**
     * @param null $entityId
     * @return array
     */
    protected function getValidationRules($entityId = null)
    {
        $childRules = $this->getChildModel()->getValidationRules($entityId);
        $pivotRules = $this->getPivotValidationRules();

        return array_merge($childRules, $pivotRules);
    }

    /**
     * Override this method to provide custom validation rules.
     *
     * @return array
     */
    protected function prepareSyncList(Collection $childModels, array $requestCollection)
    {
        $childPk = $this->getChildModel()->getPrimaryKey();

        $childModels->keyBy($childPk);

        $requestCollection = new Collection($requestCollection);
        $requestCollection->keyBy($childPk);

        $syncList = $childModels->reduce(function ($syncList, $childModel) use ($requestCollection, $childPk) {

            $key = $childModel->{$childPk};
            $requestItem = $requestCollection[$key];
            if (isset($requestItem['_pivot'])) {
                $syncList[$key] = $requestItem['_pivot'];

                return $syncList;
            }

            $syncList[] = $key;

            return $syncList;
        }, []);

        return $syncList;
    }

    /**
     * @param $childId
     * @param BaseModel $parentModel
     * @return bool
     */
    protected function childIdCanFallbackToParent($childId, BaseModel $parentModel)
    {
        $fk = $this->getRelation($parentModel)->getForeignKey();
        $parentKey = $parentModel->getKeyName();

        return $childId === false && ends_with($fk, $parentKey);
    }

    protected function getPivotValidationRules()
    {
        return [];
    }
}
