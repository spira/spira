<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 06.08.15
 * Time: 1:30
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\TestEntity;

class ChildTestController extends ChildEntityController
{
    protected $validateParentIdRule = 'uuid';
    protected $validateChildIdRule = 'uuid';
    protected $relationName = 'testMany';

    public function __construct(TestEntity $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
