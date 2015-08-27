<?php

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\Article;


class ArticleImageController extends ChildEntityController
{
    protected $relationName = 'imagesPivot';

    public function __construct(Article $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }


}