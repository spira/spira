<?php

namespace App\Http\Controllers;

use App\Models\TestEntity;
use App\Http\Transformers\EloquentModelTransformer;

class LinkedEntityTestController extends LinkedEntityController
{
    public function __construct(TestEntity $parentModel, EloquentModelTransformer $transformer)
    {
        $this->relationName = 'secondTestEntities';

        parent::__construct($parentModel, $transformer);
    }
}