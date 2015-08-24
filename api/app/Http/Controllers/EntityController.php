<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:37
 */

namespace App\Http\Controllers;

use App\Extensions\Controller\RequestValidationTrait;
use App\Models\User;
use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spira\Model\Collection\Collection;
use Spira\Model\Model\BaseModel;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Paginator\RangeRequest;
use Spira\Responder\Response\ApiResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param Request $request
     * @return ApiResponse
     */
    public function getAll(Request $request)
    {
        $collection = $this->getAllEntities();
        $collection = $this->getWithNested($collection, $request);
        
        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($collection);
    }

    public function getAllPaginated(Request $request, RangeRequest $rangeRequest)
    {
        $totalCount = $this->countEntities();
        $limit = $rangeRequest->getLimit($this->paginatorDefaultLimit, $this->paginatorMaxLimit);
        $offset = $rangeRequest->isGetLast()?$totalCount-$limit:$rangeRequest->getOffset();

        if ($request->has('q')) {
            $collection = $this->searchAllEntities($request->query('q'), $limit, $offset, $totalCount);
        } else {
            $collection = $this->getAllEntities($limit, $offset, $totalCount);
        }

        $collection = $this->getWithNested($collection, $request);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->paginatedCollection($collection, $offset, $totalCount);
    }

    /**
     * Get one entity.
     *
     * @param Request $request
     * @param  string $id
     * @return ApiResponse
     */
    public function getOne(Request $request, $id)
    {
        $model = $this->findOrFailEntity($id);
        $model = $this->getWithNested($model, $request);

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
    public function putOne(Request $request, $id)
    {
        $this->checkEntityIdMatchesRoute($request, $id, $this->getModel());

        $model = $this->findOrNewEntity($id);

        $this->validateRequest($request->all(), $this->getValidationRules());

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
        $existingModels = $this->findCollection($requestCollection);

        $modelCollection = $this->getModel()
            ->hydrateRequestCollection($requestCollection, $existingModels)
            ->each(function (BaseModel $model) {
                return $model->save();
            });

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
    public function patchOne(Request $request, $id)
    {
        $this->checkEntityIdMatchesRoute($request, $id, $this->getModel(), false);

        $model = $this->findOrFailEntity($id);

        $this->validateRequest($request->all(), $this->getValidationRules(), true);

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

        $this->validateRequestCollection($requestCollection, $this->getValidationRules(), true);

        $existingModels = $this->findOrFailCollection($requestCollection);

        $this->getModel()
            ->hydrateRequestCollection($requestCollection, $existingModels)
            ->each(function (BaseModel $model) {
                return $model->save();
            });

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

        $this->findOrFailCollection($requestCollection)
            ->each(function (BaseModel $model) {
                $model->delete();
            });

        return $this->getResponse()->noContent();
    }

    /**
     * @param $id
     * @return BaseModel
     */
    protected function findOrNewEntity($id)
    {
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
     * @param Request $request
     * @param null $limit
     * @param null $offset
     * @param null $totalCount
     * @return Collection
     */
    protected function getAllEntities($limit = null, $offset = null, &$totalCount = null)
    {
        return $this->getModel()->take($limit)->skip($offset)->get();
    }

    /**
     * @param $queryString
     * @param null $limit
     * @param null $offset
     * @param null $totalCount
     * @return \Elasticquent\ElasticquentResultCollection
     */
    protected function searchAllEntities($queryString, $limit = null, $offset = null, &$totalCount = null)
    {
        /* @var ElasticquentTrait $model */
        $model = $this->getModel();

        $searchResults = $model->searchByQuery([
            'match_phrase' => [
                '_all' => $queryString
            ]
        ], null, null, $limit, $offset);

        if ($searchResults->totalHits() === 0) {
            throw new NotFoundHttpException(sprintf("No results found with query `%s` for model `%s`", $queryString, get_class($model)));
        }

        if (isset($totalCount) && $searchResults->totalHits() < $totalCount) {
            $totalCount = $searchResults->totalHits();
        }

        return $searchResults;
    }

    /**
     * @param $requestCollection
     * @return Collection
     */
    protected function findOrFailCollection($requestCollection)
    {
        $ids = $this->getIds($requestCollection, $this->getModel()->getKeyName());

        if (empty($ids)) {
            throw $this->notFoundManyException($ids, $this->getModel()->newCollection(), $this->getModel()->getKeyName());
        }

        $models = $this->getModel()->findMany($ids);

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
        $ids = $this->getIds($requestCollection, $this->getModel()->getKeyName());
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
     * @return array
     */
    protected function getValidationRules()
    {
        return $this->getModel()->getValidationRules();
    }
}
