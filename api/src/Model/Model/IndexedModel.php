<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Model\Model;

use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\Collection;
use Spira\Model\Collection\IndexedCollection;

abstract class IndexedModel extends BaseModel
{
    use ElasticquentTrait;

    protected $indexNested = [];

    protected $mappingProperties = [
        'title' => [
            'type' => 'string',
            'analyzer' => 'standard',
        ],
    ];

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
     * @return bool
     */
    public static function indexExists()
    {
        $instance = new static;

        $params = [
            'index' => $instance->getIndexName(),
        ];

        return $instance->getElasticSearchClient()->indices()->exists($params);
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

        if (! empty($this->indexNested)) {
            // We have to do this because we don't know if the relation is one to or one to many. If it is one to one
            // we don't want to strip out the keys.
            foreach ($this->indexNested as $nestedModelName) {
                $results = $this->$nestedModelName()->getResults();

                if ($results instanceof Collection) {
                    $relations[snake_case($nestedModelName)] = array_values($results->toArray());
                } else {
                    $relations[snake_case($nestedModelName)] = $results->toArray();
                }
            }
        }

        $attributes = $this->attributesToArray();

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
