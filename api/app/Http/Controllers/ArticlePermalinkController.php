<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 05.08.15
 * Time: 18:03
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Repositories\ArticleRepository;

class ArticlePermalinkController extends ChildEntityController
{
    protected $validateRequestRule = 'required|string';
    protected $validateChildRequestRule = 'required|string';
    protected $relationName = 'permalinks';

    public function __construct(ArticleRepository $repository, EloquentModelTransformer $transformer)
    {
        parent::__construct($repository, $transformer);
    }
}
