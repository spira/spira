<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Transformers\ArticleCommentTransformer;

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
     * @param  Article                   $parentModel
     * @param  ArticleCommentTransformer $transformer
     *
     * @return void
     */
    public function __construct(Article $parentModel, ArticleCommentTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
