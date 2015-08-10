<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 06.08.15
 * Time: 1:30
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\SecondTestEntity;
use App\Models\TestEntity;

class ChildTestController extends ChildEntityController
{
    protected $validateParentRequestRule = 'uuid';
    protected $validateChildRequestRule = 'uuid';

    public function __construct(TestEntity $parentModel, SecondTestEntity $childModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $childModel, $transformer);
    }


}
