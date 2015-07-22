<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 22.07.15
 * Time: 0:31
 */

namespace App\Specifications;


use App\Models\Article;
use App\Models\ArticlePermalink;
use Illuminate\Database\Eloquent\Builder;
use Spira\Repository\Model\BaseModel;
use Spira\Repository\Specification\EloquentSpecificationInterface;
use Spira\Repository\Specification\SpecificationIsNotImplementedException;


class ArticlePermalinkSpecification implements EloquentSpecificationInterface
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @param string $uri
     */
    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function attachCriteriaToBuilder(Builder $builder)
    {
        if (!($builder->getModel() instanceof Article)){
            throw new \InvalidArgumentException('Provided not an Article object');
        }

        $query = $builder->getQuery();
        $tableName = $builder->getModel()->getTable();
        $joinTableName = ArticlePermalink::getTableName();
        $query->join($joinTableName,$joinTableName.'.article_id','=',$tableName.'.article_id','left','uri');
        $query->where($tableName.'.article_id','=',$this->uri);
        $query->orWhere($joinTableName.'.uri','=',$this->uri);

        return $builder;
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