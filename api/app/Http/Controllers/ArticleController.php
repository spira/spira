<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 23:39
 */

namespace App\Http\Controllers;

use App\Http\Transformers\ArticleTransformer;
use App\Models\Article;

class ArticleController extends EntityController
{
    protected $validateIdRule = 'required|string';

    /**
     * Assign dependencies.
     * @param Article $model
     * @param ArticleTransformer $transformer
     */
    public function __construct(Article $model, ArticleTransformer $transformer)
    {
        parent::__construct($model, $transformer);
    }

}
