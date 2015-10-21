<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spira\Model\Model\BaseModel;
use Elasticquent\ElasticquentTrait;
use Spira\Model\Collection\Collection;
use Spira\Responder\Response\ApiResponse;
use Spira\Responder\Paginator\RangeRequest;
use Spira\Responder\Contract\TransformerInterface;
use App\Extensions\Controller\RequestValidationTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class EntityController extends ApiController
{
    use RequestValidationTrait;

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
        $this->checkPermission(static::class.'@getAll', ['model' => $collection]);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($collection);
    }

    public function getAllPaginated(Request $request, RangeRequest $rangeRequest)
    {
        $totalCount = $this->countEntities();
        $limit = $rangeRequest->getLimit($this->paginatorDefaultLimit, $this->paginatorMaxLimit);
        $offset = $rangeRequest->isGetLast() ? $totalCount - $limit : $rangeRequest->getOffset();

        if ($request->has('q')) {
            $collection = $this->searchAllEntities(base64_decode($request->query('q')), $limit, $offset, $totalCount);
        } else {
            $collection = $this->getAllEntities($limit, $offset);
        }

        $collection = $this->getWithNested($collection, $request);
        $this->checkPermission(static::class.'@getAllPaginated', ['model' => $collection]);

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
        $this->checkPermission(static::class.'@getOne', ['model' => $model]);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($model);
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
        $this->validateRequest($request->json()->all(), $this->getValidationRules());
        $model->fill($request->json()->all());
        $this->checkPermission(static::class.'@postOne', ['model' => $model]);
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

        $this->validateRequest($request->json()->all(), $this->getValidationRules());

        $model->fill($request->json()->all());

        $this->checkPermission(static::class.'@putOne', ['model' => $model]);
        $model->save();

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
        $requestCollection = $request->json()->all();

        $this->validateRequestCollection($requestCollection, $this->getValidationRules());
        $existingModels = $this->findCollection($requestCollection);

        $modelCollection = $this->getModel()
            ->hydrateRequestCollection($requestCollection, $existingModels);

        $this->checkPermission(static::class.'@putMany', ['model' => $modelCollection]);

        $modelCollection->each(function (BaseModel $model) use ($request) {
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

        $this->validateRequest($request->json()->all(), $this->getValidationRules(), true);

        $model->fill($request->json()->all());
        $this->checkPermission(static::class.'@patchOne', ['model' => $model]);
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
        $requestCollection = $request->json()->all();

        $this->validateRequestCollection($requestCollection, $this->getValidationRules(), true);

        $existingModels = $this->findOrFailCollection($requestCollection);

        $modelsCollection = $this->getModel()
            ->hydrateRequestCollection($requestCollection, $existingModels);

        $this->checkPermission(static::class.'@patchMany', ['model' => $existingModels]);

        $modelsCollection->each(function (BaseModel $model) {
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
        $entity = $this->findOrFailEntity($id);

        $this->checkPermission(static::class.'@deleteOne', ['model' => $entity]);

        $entity->delete();

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
        $requestCollection = $request->json()->all();

        $modelsCollection = $this->findOrFailCollection($requestCollection);

        $this->checkPermission(static::class.'@deleteMany', ['model' => $modelsCollection]);

        $modelsCollection->each(function (BaseModel $model) {
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
     * @param null $limit
     * @param null $offset
     * @return Collection
     */
    protected function getAllEntities($limit = null, $offset = null)
    {
        return $this->getModel()->take($limit)->skip($offset)->get();
    }

    /**
     * @param $query
     * @param null $limit
     * @param null $offset
     * @param null $totalCount
     * @return \Elasticquent\ElasticquentResultCollection
     */
    protected function searchAllEntities($query, $limit = null, $offset = null, &$totalCount = null)
    {
        /* @var ElasticquentTrait $model */
        $model = $this->getModel();

        $queryArray = json_decode($query, true);

        if (is_array($queryArray)) { // Complex query
            $searchResults = $model->complexSearch([
                'index' => $model->getIndexName(),
                'type' => $model->getTypeName(),
                'body' => $this->translateQuery($queryArray),
            ]);
        } else {
            $searchResults = $model->searchByQuery([
                'match_phrase' => [
                    '_all' => $query,
                ],
            ], null, null, $limit, $offset);
        }

        if ($searchResults->totalHits() === 0) {
            throw new NotFoundHttpException(sprintf('No results found for model `%s`', get_class($model)));
        }

        if (isset($totalCount) && $searchResults->totalHits() < $totalCount) {
            $totalCount = $searchResults->totalHits();
        }

        return $searchResults;
    }

    /**
     * Takes a query and translates it into a query that elastic search understands.
     *
     * Expect query to be in form, e.g.:
     * {
     *      _all: [ "stringA", "stringB" ],
     *      someField: [ "stringA" ],
     *      _nestedEntity: { key: [ "stringA", "stringB" ]
     * }
     *
     * Notes:
     * - Empty values will be removed from search completely.
     * - You must pass an array, even if it only contains 1 string.
     *
     * @param $query
     * @return mixed
     */
    private function translateQuery($query)
    {
        $processedQuery['query']['bool']['must'] = [];

        foreach ($query as $key => $value) {
            if (Str::startsWith($key, '_') && $key != '_all') { // Nested entity
                reset($value);
                $fieldKey = key($value);

                foreach ($value[$fieldKey] as $fieldValue) {
                    if (! empty($fieldValue)) {
                        $snakeKey = snake_case(substr($key, 1));
                        array_push($processedQuery['query']['bool']['must'],
                            [
                                'nested' => [
                                    'path' => $snakeKey,
                                    'query' => [
                                        'bool' => [
                                            'must' => [
                                                'match' => [
                                                    $snakeKey.'.'.snake_case($fieldKey) => $fieldValue,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        );
                    }
                }
            } else {
                foreach ($value as $matchValue) {
                    if (! empty($matchValue)) {
                        array_push($processedQuery['query']['bool']['must'], [
                            'match' => [
                                snake_case($key) => $matchValue,
                            ],
                        ]);
                    }
                }
            }
        }

        if (empty($processedQuery['query']['bool']['must'])) { // No search params have been supplied, match all
            return ['query' => ['match_all' => []]];
        }

        return $processedQuery;
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
