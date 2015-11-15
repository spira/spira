<?php


namespace App\Http\Controllers;


use App\Http\Transformers\EloquentModelTransformer;
use App\Models\Article;

class ArticleBookmarkController extends ChildEntityController
{
    protected $relationName = 'bookmark';

    public function __construct(Article $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}