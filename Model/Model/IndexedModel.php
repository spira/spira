<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\Model\Model;

use Carbon\Carbon;
use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\Collection;
use Spira\Core\Model\Collection\IndexedCollection;

abstract class IndexedModel extends BaseModel
{
    use ElasticquentTrait;

    protected $indexRelations = [];

    protected $mappingProperties = [
        'title' => [
            'type' => 'string',
            'analyzer' => 'standard',
        ],
    ];

    public static function createCustomIndexes()
    {
    }

    public static function deleteCustomIndexes()
    {
    }

    /**
     * Create a new Eloquent Collection instance with ElasticquentCollectionTrait.
     *
     * @param  array $models
     * @return IndexedCollection
     */
    public function newCollection(array $models = [])
    {
        return new IndexedCollection($models, static::class);
    }

    /**
     * Check if index exists.
     * @param null $indexName
     * @return bool
     */
    public static function indexExists($indexName = null)
    {
        $instance = new static;

        if (! $indexName) {
            $indexName = $instance->getIndexName();
        }

        $params = [
            'index' => $indexName,
        ];

        return $instance->getElasticSearchClient()->indices()->exists($params);
    }

    public static function deleteIndex($indexName = null)
    {
        $instance = new static;

        if (! $indexName) {
            $indexName = $instance->getIndexName();
        }

        $index = [
            'index' => $indexName,
        ];

        return $instance->getElasticSearchClient()->indices()->delete($index);
    }

    /**
     * Remove all of this entity from the index.
     * @return bool
     */
    public static function removeAllFromIndex()
    {
        return self::mappingExists() && self::deleteMapping();
    }

    /**
     * Get the count of this entity in the index.
     * @return mixed
     */
    public function countIndex()
    {
        $instance = new static;

        $params = [
            'index' => $instance->getIndexName(),
        ];

        return $instance->getElasticSearchClient()->count($params);
    }

    public function getIndexDocumentData()
    {
        $relations = [];

        if (! empty($this->indexRelations)) {
            // We have to do this because we don't know if the relation is one to or one to many. If it is one to one
            // we don't want to strip out the keys.
            foreach ($this->indexRelations as $nestedModelName) {
                $results = $this->$nestedModelName()->getResults();

                // @Todo: Have to transform instances of Carbon into date-times
                if ($results instanceof Collection) {
                    $relations[snake_case($nestedModelName)] = array_values($results->toArray());
                } else {
                    $relations[snake_case($nestedModelName)] = $results->toArray();
                }
            }
        }

        $attributes = $this->attributesToArray();

        // for some reason laravel converts to string, then back to datetime when doing toArray. This reverts back to
        // an ISO8601 formatted date for elastic to interpret
        foreach ($this->getDates() as $dateKey) {
            if (isset($attributes[$dateKey]) && $attributes[$dateKey] instanceof Carbon) {
                $attributes[$dateKey] = $attributes[$dateKey]->toIso8601String();
            }
        }

        return array_merge($attributes, $relations);
    }

    protected static function boot()
    {
        parent::boot(); //register the parent event handlers first

        static::created(
            function (IndexedModel $model) {
                $model->addToIndex();

                return true;
            }, PHP_INT_MAX
        );

        static::deleted(
            function (IndexedModel $model) {
                $model->removeFromIndex();

                return true;
            }, PHP_INT_MAX
        );

        static::updated(
            function (IndexedModel $model) {
                $model->updateIndex();

                return true;
            }, PHP_INT_MAX
        );
    }
}
