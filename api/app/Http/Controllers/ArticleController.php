<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 23:39
 */

namespace app\Http\Controllers;


use App\Http\Responder\Responder;
use App\Repositories\ArticleRepository;
use App\Specifications\ArticlePermalinkSpecification;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * Get one entity.
     *
     * @param  string $id
     * @return Response
     */
    public function getOne($id)
    {
        $model = $this->repository
            ->findSpecifying(new ArticlePermalinkSpecification($id))
            ->first();

        if (!$model){
            $this->getResponder()->errorNotFound();
        }

        return $this->getResponder()->item($model);
    }
}