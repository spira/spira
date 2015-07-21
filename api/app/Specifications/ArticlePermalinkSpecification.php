<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 22.07.15
 * Time: 0:31
 */

namespace App\Specifications;


use App\Models\Article;
use Spira\Repository\Model\BaseModel;
use Spira\Repository\Specification\EloquentSpecificationInterface;
use Spira\Repository\Specification\SpecificationIsNotImplementedException;


class ArticlePermalinkSpecification implements EloquentSpecificationInterface
{
    private $uri;

    /**
     * @param $uri
     */
    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @param BaseModel $entity
     * @return BaseModel
     */
    public function attachCriteriaToModel(BaseModel $entity)
    {
        if (!($entity instanceof Article)){
            throw new \InvalidArgumentException('Provided not an Article object');
        }


    }

    /**
     * @param BaseModel $entity
     * @return bool
     * @throws SpecificationIsNotImplementedException
     */
    public function isSatisfiedBy(BaseModel $entity)
    {
        if (!($entity instanceof Article)){
            throw new \InvalidArgumentException('Provided not an Article object');
        }

        if ($entity->permalinkRelation && $entity->permalinkRelation->uri === $this->uri){
            return true;
        }

        foreach ($entity->previousPermalinksRelations as $permalink)
        {
            if ($permalink->uri === $this->uri){
                return true;
            }
        }

        return false;
    }
}