<?php

namespace Spira\Repository\Specification;

use Spira\Repository\Model\BaseModel;

interface SpecificationInterface
{
    /**
     * @param BaseModel $entity
     * @return bool
     * @throws SpecificationIsNotImplementedException
     */
    public function isSatisfiedBy(BaseModel $entity);
}