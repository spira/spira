<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 23:39
 */

namespace app\Http\Controllers;


use App\Http\Validators\TestEntityValidator;
use App\Repositories\ArticleRepository;

class ArticleController extends BaseController
{
    /**
     * Assign dependencies.
     * @param TestEntityValidator $validator
     * @param ArticleRepository $repository
     */
    public function __construct(TestEntityValidator $validator, ArticleRepository $repository)
    {
        $this->validator = $validator;
        $this->repository = $repository;
    }
}