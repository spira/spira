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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spira\Repository\Model\BaseModel;

class ArticleController extends EntityController
{
    protected $validateRequestRule = 'required|string';

    /**
     * Assign dependencies.
     * @param Article $model
     * @param ArticleTransformer $transformer
     */
    public function __construct(Article $model, ArticleTransformer $transformer)
    {
        parent::__construct($model,$transformer);
    }

    /**
     * @param $id
     * @return BaseModel
     * @throws ModelNotFoundException
     */
    protected function getEntityById($id)
    {
        /** @var Article $model */
        $model = $this->getModel();
        return $model->findByPermalink($id);
    }


}
