<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 20.08.15
 * Time: 19:10
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\Tag;

class TagController extends EntityController
{
    public function __construct(Tag $model, EloquentModelTransformer $transformer)
    {
        parent::__construct($model, $transformer);
    }
}
