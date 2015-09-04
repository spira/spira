<?php

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\Article;

class ArticlePermalinkController extends ChildEntityController
{
    protected $relationName = 'permalinks';

    public function __construct(Article $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
