<?php

/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 23:39.
 */

namespace App\Http\Controllers;

use App\Extensions\JWTAuth\JWTAuth;
use App\Http\Transformers\ArticleTransformer;
use App\Models\Article;

class ArticleController extends EntityController
{
    /**
     * @var JWTAuth
     */
    private $auth;

    /**
     * Assign dependencies.
     * @param Article $model
     * @param ArticleTransformer $transformer
     * @param JWTAuth $auth
     */
    public function __construct(Article $model, ArticleTransformer $transformer, JWTAuth $auth)
    {
        parent::__construct($model, $transformer);
        $this->auth = $auth;
    }
}
