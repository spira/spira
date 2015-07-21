<?php

namespace Spira\Repository\Specification;

use Spira\Repository\Model\BaseModel;

interface EloquentSpecificationInterface extends SpecificationInterface
{
    /**
     * @param BaseModel $entity
     * @return BaseModel
     */
    public function attachCriteriaToModel(BaseModel $entity);
}