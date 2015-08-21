<?php namespace Spira\Model\Model;

use Elasticquent\ElasticquentTrait;

abstract class IndexedModel extends BaseModel
{
    use ElasticquentTrait {
        newCollection as newElasticquentCollection;
    }


    public function newCollection()
    {
        return parent::newCollection();
    }
}
