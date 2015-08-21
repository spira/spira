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
     * Type Exists.
     *
     * Does this type exist?
     *
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
}
