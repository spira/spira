<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Transformers\EloquentModelTransformer;

class ArticleCommentController extends ChildEntityController
{
    /**
     * Name in the parent model of this entity.
     *
     * @var string
     */
    protected $relationName = 'comments';

    /**
     * Set dependencies.
     *
     * @param  Article                  $parentModel
     * @param  EloquentModelTransformer $transformer
     *
     * @return void
     */
    public function __construct(Article $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
