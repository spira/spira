<?php

namespace Spira\Repository\Specification;

use Illuminate\Database\Eloquent\Builder;

interface EloquentSpecificationInterface extends SpecificationInterface
{
    /**
     * @param Builder $builder
     * @return Builder
     */
    public function attachCriteriaToBuilder(Builder $builder);
}