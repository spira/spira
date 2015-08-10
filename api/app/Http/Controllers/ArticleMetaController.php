<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 05.08.15
 * Time: 1:41
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\Article;
use App\Models\ArticleMeta;
use Spira\Repository\Model\BaseModel;

class ArticleMetaController extends ChildEntityController
{
    protected $validateParentRequestRule = 'required|string';
    protected $validateChildRequestRule = 'required|string';


    public function __construct(Article $parentModel, ArticleMeta $childModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $childModel, $transformer);
    }

    /**
     * @param $id
     * @return BaseModel
     */
    protected function getParentEntityById($id)
    {
        /** @var Article $model */
        $model = $this->getParentModel();
        return $model->findByPermalink($id);
    }

}
