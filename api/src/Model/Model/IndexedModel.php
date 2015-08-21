<?php namespace Spira\Model\Model;

use Elasticquent\ElasticquentTrait;
use Spira\Model\Collection\IndexedCollection;

abstract class IndexedModel extends BaseModel
{
    use ElasticquentTrait;

    /**
     * Create a new Eloquent Collection instance with ElasticquentCollectionTrait.
     *
     * @param  array  $models
     * @return IndexedCollection
     */
    public function newCollection(array $models = [])
    {
        return new IndexedCollection($models, static::class);
    }

    /**
     * Check if index exists
     * @return bool
     */
    public static function indexExists()
    {
        $instance = new static;

        $params = array(
            'index' => $instance->getIndexName(),
        );

        return $instance->getElasticSearchClient()->indices()->exists($params);
    }

    /**
     * Remove all of this entity from the index
     * @return bool
     */
    public static function removeAllFromIndex()
    {
        return self::mappingExists() && self::deleteMapping();
    }

    /**
     * Get the count of this entity in the index
     * @return mixed
     */
    public function countIndex()
    {
        $instance = new static;

        $params = array(
            'index' => $instance->getIndexName(),
        );

        return $instance->getElasticSearchClient()->count($params);
    }

}
