<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 23:39
 */

namespace App\Http\Controllers;

use App\Http\Transformers\ArticleTransformer;
use App\Repositories\ArticleRepository;

class ArticleController extends EntityController
{
    protected $validateRequestRule = 'required|string';

    /**
     * Assign dependencies.
     * @param ArticleRepository $repository
     * @param ArticleTransformer $transformer
     */
    public function __construct(ArticleRepository $repository, ArticleTransformer $transformer)
    {
        $this->repository = $repository;
        $this->transformer = $transformer;
    }
}
