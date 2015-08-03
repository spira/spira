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
use App\Repositories\ArticleRepository;

class ArticleController extends ApiController
{
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

    public function getPermalinks($id)
    {
        /** @var Article $article */
        $article = $this->repository->find($id);
        return $this->getResponse()
            ->transformer($this->transformer)
            ->collection($article->permalinks);
    }
}
