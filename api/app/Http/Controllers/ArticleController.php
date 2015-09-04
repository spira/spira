<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
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
