<?php

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\TestEntity;

class ChildTestController extends ChildEntityController
{
    protected $relationName = 'testMany';
    protected $validateParentIdRule = 'uuid';
    protected $validateChildIdRule = 'uuid';

    public function __construct(TestEntity $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
