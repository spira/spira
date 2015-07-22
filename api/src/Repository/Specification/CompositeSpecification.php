<?php

namespace Spira\Repository\Specification;

use Illuminate\Database\Eloquent\Builder;
use Spira\Repository\Model\BaseModel;

class CompositeSpecification implements EloquentSpecificationInterface
{

    /**
     * @var EloquentSpecificationInterface[]
     */
    protected $specifications = array();


    /**
     * @param BaseModel $entity
     * @return bool
     * @throws SpecificationIsNotImplementedException
     */
    public function isSatisfiedBy(BaseModel $entity)
    {
        foreach ($this->specifications as $specification)
        {
            if (!$specification->isSatisfiedBy($entity)){
                return false;
            }
        }

        return true;
    }

    public function add(SpecificationInterface $specification)
    {
        $this->specifications[] = $specification;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function attachCriteriaToBuilder(Builder $builder)
    {
        foreach($this->specifications as $specification)
        {
            $specification->attachCriteriaToBuilder($builder);
        }

        return $builder;
    }
}