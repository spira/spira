<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spira\Model\Collection\Collection;
use Spira\Model\Model\BaseModel;
use Spira\Responder\Response\ApiResponse;

class ArticleImageController extends ChildEntityController
{
    public function getAll(Request $request, $id, $group = null)
    {
        $model = $this->findParentEntity($id);
        $childEntities = $this->findAllChildren($model, $group);
        $childEntities = $this->getWithNested($childEntities, $request);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($childEntities);
    }

    /**
     * Put many entities.
     *
     * @param  Request $request
     * @param string $id
     * @param $group
     * @return ApiResponse
     */
    public function putMany(Request $request, $id, $group)
    {
        $parent = $this->findParentEntity($id);

        $requestCollection = $request->data;
        $this->validateRequestCollection($requestCollection, $this->getValidationRules());

        $existingChildModels = Image::whereIn('image_id', $this->getIds($requestCollection, 'image_id'))->get();

        $childModels = $this->getChildModel()
            ->hydrateRequestCollection($requestCollection, $existingChildModels)
            ->each(function (BaseModel $model) {
                if (!$model->exists) {
                    $model->save();
                }
            });

        foreach ($childModels as $childModel) {
            $this->getRelation($parent)->attach($childModel,['group_type'=>$group]);
        }

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->createdCollection($childModels);
    }

    /**
     * Delete an entity.
     *
     * @param  string $id
     * @param string $childId
     * @param $group
     * @return ApiResponse
     * @throws \Exception
     */
    public function deleteOne($id, $childId, $group)
    {
        $model = $this->findParentEntity($id);
        $childModel = $this->findOrFailChildEntity($childId, $model, $group);

        $childModel->pivot->delete();

        return $this->getResponse()->noContent();
    }


    /**
     * @param $parent
     * @param null $group
     * @return Collection
     */
    protected function findAllChildren($parent, $group = null)
    {
        $relation = $this->getRelation($parent);
        if (!is_null($group)){
            $relation->wherePivot('group_type','=',$group);
        }

        return $relation->getResults();
    }

    /**
     * @param $id
     * @param BaseModel $parent
     * @param $group
     * @return BaseModel
     */
    protected function findOrFailChildEntity($id, BaseModel $parent, $group)
    {
        try {
            return $this->getRelation($parent)->wherePivot('group_type','=',$group)->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getChildModel()->getKeyName());
        }
    }
}