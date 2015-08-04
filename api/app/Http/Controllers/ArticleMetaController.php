<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 05.08.15
 * Time: 1:41
 */

namespace App\Http\Controllers;


use App\Http\Transformers\EloquentModelTransformer;
use App\Repositories\ArticleRepository;

class ArticleMetaController extends ChildEntityController
{
    protected $validateChildRequestRule = 'required|string';
    protected $relationName = 'metas';

    public function __construct(ArticleRepository $repository, EloquentModelTransformer $transformer)
    {
        parent::__construct($repository, $transformer);
    }

}