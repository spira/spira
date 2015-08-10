<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 04.08.15
 * Time: 20:23
 */

namespace App\Http\Controllers;

use App\Extensions\Controller\RequestValidationTrait;
use App\Helpers\ModelHelper;
use App\Models\ChildBaseModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spira\Repository\Collection\Collection;
use Spira\Repository\Model\BaseModel;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Response\ApiResponse;

class ChildEntityController extends ApiController
{
    use RequestValidationTrait;

    protected $validateParentRequestRule = 'uuid';
    protected $validateChildRequestRule = 'uuid';

    /**
     * @var BaseModel
     */
    protected $parentModel;

    /**
     * @var ChildBaseModel
     */
    protected $childModel;

    public function __construct(BaseModel $parentModel, ChildBaseModel $childModel, TransformerInterface $transformer)
    {
        $this->parentModel = $parentModel;
        $this->childModel = $childModel;
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
        $this->validateId($id, $this->getParentModel()->getKeyName(), $this->validateParentRequestRule);
        try {
            $model =  $this->getParentEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }

        $childEntities = $this->getAllChildEntitiesByParent($model);

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
        $this->validateId($id, $this->getParentModel()->getKeyName(), $this->validateParentRequestRule);
        try {
            $model =  $this->getParentEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }

        $this->validateId($childId, $this->getChildModel()->getKeyName(), $this->validateChildRequestRule);

        try{
            $childModel = $this->getChildEntityByIdAndParent($childId, $model);
        }catch (ModelNotFoundException $e){
            throw $this->notFoundException($this->getChildModel()->getKeyName());
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($childModel)
            ;
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
        $this->validateId($id, $this->getParentModel()->getKeyName(), $this->validateParentRequestRule);
        try {
            $model =  $this->getParentEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }

        $childModel = $this->getChildModel()->newInstance();
        $childModel->fill($request->all());
        $childModel->attachParent($model);
        $childModel->save();

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdItem($childModel);
    }

    /**
     * Put an entity.
     *
     * @param  string $id
     * @param $childId
     * @param  Request $request
     * @return ApiResponse
     */
    public function putOne($id, $childId, Request $request)
    {
        $this->validateId($id, $this->getParentModel()->getKeyName(), $this->validateParentRequestRule);
        try {
            $model =  $this->getParentEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }

        $this->validateId($childId, $this->getChildModel()->getKeyName(), $this->validateChildRequestRule);
        try{
            $childModel = $this->getChildEntityByIdAndParent($childId, $model);
        }catch (ModelNotFoundException $e){
            $childModel = $this->getChildModel()->newInstance();
        }

        /** @var ChildBaseModel $childModel */
        $childModel->fill($request->all());
        $childModel->attachParent($model);
        $childModel->save();

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

        $this->validateId($id, $this->getParentModel()->getKeyName(), $this->validateParentRequestRule);
        try {
            $model =  $this->getParentEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }

        $childKey = $this->getChildModel()->getKeyName();
        $ids = $this->getIds($requestCollection, $childKey, $this->validateChildRequestRule);
        $childModels = [];
        if (!empty($ids)) {
            $childModels = $this->getManyChildEntitiesByIdsAndParent($ids,$model);
        }

        $putModels = [];
        foreach ($requestCollection as $requestEntity) {
            $id = isset($requestEntity[$childKey])?$requestEntity[$childKey]:null;
            if ($id && !empty($childModels) && $childModels->has($id)) {
                $childModel = $childModels->get($id);
            } else {
                $childModel = $this->getChildModel()->newInstance();
            }
            /** @var ChildBaseModel $childModel */
            $childModel->fill($requestEntity);
            $childModel->attachParent($model);
            $putModels[] = $childModel;
        }
        ModelHelper::saveMany($putModels);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdCollection($putModels);
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
        $this->validateId($id, $this->getParentModel()->getKeyName(), $this->validateParentRequestRule);
        try {
            $model =  $this->getParentEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }

        $this->validateId($childId, $this->getChildModel()->getKeyName(), $this->validateChildRequestRule);

        try{
            $childModel = $this->getChildEntityByIdAndParent($childId, $model);
        }catch (ModelNotFoundException $e){
            throw $this->notFoundException($this->getChildModel()->getKeyName());
        }

        /** @var ChildBaseModel $childModel */
        $childModel->fill($request->all());
        $childModel->attachParent($model);
        $childModel->save();

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

        $this->validateId($id, $this->getParentModel()->getKeyName(), $this->validateParentRequestRule);
        try {
            $model =  $this->getParentEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }

        $childKey = $this->getChildModel()->getKeyName();

        $ids = $this->getIds($requestCollection, $childKey, $this->validateChildRequestRule);
        $childModels = [];
        if (!empty($ids)) {
            $childModels = $this->getManyChildEntitiesByIdsAndParent($ids,$model);
        }

        if (!empty($childModels) && $childModels->count() !== count($ids)) {
            throw $this->notFoundManyException($ids, $childModels, $childKey);
        }

        foreach ($requestCollection as $requestEntity) {
            $id = $requestEntity[$childKey];
            $childModel = $childModels->get($id);

            /** @var ChildBaseModel $childModel */
            $childModel->fill($requestEntity);
            $childModel->attachParent($model);
        }

        ModelHelper::saveMany($childModels);

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
        $this->validateId($id, $this->getParentModel()->getKeyName(), $this->validateParentRequestRule);
        try {
            $model =  $this->getParentEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }

        $this->validateId($childId, $this->getChildModel()->getKeyName(), $this->validateChildRequestRule);

        try{
            $childModel = $this->getChildEntityByIdAndParent($childId, $model);
        }catch (ModelNotFoundException $e){
            throw $this->notFoundException($this->getChildModel()->getKeyName());
        }

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

        $this->validateId($id, $this->getParentModel()->getKeyName(), $this->validateParentRequestRule);
        try {
            $model =  $this->getParentEntityById($id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getParentModel()->getKeyName());
        }

        $childKey = $this->getChildModel()->getKeyName();

        $ids = $this->getIds($requestCollection, $childKey, $this->validateChildRequestRule);
        $childModels = [];
        if (!empty($ids)) {
            $childModels = $this->getManyChildEntitiesByIdsAndParent($ids,$model);
        }


        if (!empty($childModels) && $childModels->count() !== count($ids)) {
            throw $this->notFoundManyException($ids, $childModels, $childKey);
        }

        ModelHelper::deleteMany($childModels);

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
     * @return ChildBaseModel
     */
    public function getChildModel()
    {
        return $this->childModel;
    }

    /**
     * @param $id
     * @return BaseModel
     */
    protected function getParentEntityById($id)
    {
        return $this->getParentModel()->findOrFail($id);
    }

    /**
     * @param $id
     * @param $parent
     * @return BaseModel
     */
    protected function getChildEntityByIdAndParent($id, $parent)
    {
        return $this->getChildModel()->findByIdAndParent($id,$parent);
    }

    /**
     * @param $ids
     * @param $parent
     * @return Collection
     */
    protected function getManyChildEntitiesByIdsAndParent($ids, $parent)
    {
        return $this->getChildModel()->findManyByIdsAndParent($ids,$parent);
    }

    /**
     * @param $parent
     * @return Collection
     */
    protected function getAllChildEntitiesByParent($parent)
    {
        return $this->getChildModel()->findAllByParent($parent);
    }

}
