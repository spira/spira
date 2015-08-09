<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 06.08.15
 * Time: 1:30
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Repositories\TestRepository;

class ChildTestController extends ChildEntityController
{
    protected $validateRequestRule = 'uuid';
    protected $validateChildRequestRule = 'uuid';
    protected $relationName = 'testMany';

    public function __construct(TestRepository $repository, EloquentModelTransformer $transformer)
    {
        parent::__construct($repository, $transformer);
    }
}
