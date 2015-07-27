<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 23:39
 */

namespace App\Http\Controllers;

use App\Http\Responder\Responder;
use App\Models\Article;
use App\Repositories\ArticleRepository;

class ArticleController extends ApiController
{
    /**
     * Assign dependencies.
     * @param ArticleRepository $repository
     * @param Responder $responder
     */
    public function __construct(ArticleRepository $repository, Responder $responder)
    {
        $this->repository = $repository;
        $this->responder = $responder;
    }

    public function getPermalinks($id)
    {
        /** @var Article $article */
        $article = $this->repository->find($id);
        return $this->responder->collection($article->permalinks);
    }
}
