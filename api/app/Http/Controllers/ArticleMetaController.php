<?php

/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 05.08.15
 * Time: 1:41.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\Article;

class ArticleMetaController extends ChildEntityController
{
    protected $relationName = 'metas';

    public function __construct(Article $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
