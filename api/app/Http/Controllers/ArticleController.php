<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 23:39
 */

namespace App\Http\Controllers;

use App\Exceptions\ValidationException;
use App\Http\Transformers\ArticleTransformer;
use App\Models\Article;
use App\Models\ArticleMeta;
use App\Repositories\ArticleRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spira\Responder\Response\ApiResponse;

class ArticleController extends ApiController
{
    /**
     * @var string
     */
    private $metaKeyName = null;

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

    /**
     * @param $id
     * @return ApiResponse
     */
    public function getPermalinks($id)
    {
        $article = $this->getArticle($id);
        return $this->getResponse()
            ->transformer($this->transformer)
            ->collection($article->permalinks);
    }

    /**
     * @param $id
     * @return ApiResponse
     */
    public function getMetas($id)
    {
        $article = $this->getArticle($id);
        return $this->getResponse()
            ->transformer($this->transformer)
            ->collection($article->metas);
    }

    /**
     * @param $id
     * @param Request $request
     * @return ApiResponse
     * @throws \Exception
     * @throws \Spira\Repository\Repository\RepositoryException
     */
    public function putMetas($id, Request $request)
    {
        $article = $this->getArticle($id);
        $requestCollection = $request->data;

        $metaNames = $this->getIds($requestCollection, $this->getMetaKeyName(), true, 'required|string');

        foreach ($metaNames as $metaName)
        {
            if (!$meta = $article->metas->find($metaName)){
                $meta = new ArticleMeta();
            }
            $meta->fill($request->all());

            if (!$meta->exists){
                $article->metas->add($meta);
            }
        }

        $this->repository->save($article);
        return $this->getResponse()->created();
    }

    /**
     * @param $id
     * @param $metaName
     * @return ApiResponse
     * @throws \Exception
     * @throws \Spira\Repository\Repository\RepositoryException
     */
    public function deleteMeta($id, $metaName)
    {
        $article = $this->getArticle($id);
        $meta = $article->metas->get($metaName);
        $article->metas->remove($meta);
        $this->repository->save($article);
        return $this->getResponse()->noContent();
    }

    /**
     * @param string $id
     * @throws ValidationException
     * @return Article
     */
    private function getArticle($id)
    {
        $this->validateId($id, $this->getKeyName());
        try{
            /** @var Article $article */
            $article = $this->repository->find($id);
            return $article;
        }catch (ModelNotFoundException $e){
            throw $this->notFoundException($this->getKeyName());
        }
    }

    /**
     * @return string
     */
    private function getMetaKeyName()
    {
        if (is_null($this->metaKeyName)){
            $meta = new ArticleMeta();
            $this->metaKeyName = $meta->getKeyName();
        }
        return $this->metaKeyName;
    }
}
