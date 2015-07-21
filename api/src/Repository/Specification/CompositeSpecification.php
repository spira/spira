<?php

namespace Spira\Repository\Specification;

use Spira\Repository\Model\BaseModel;

class CompositeSpecification implements SpecificationInterface
{

    /**
     * @var SpecificationInterface[]
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

}