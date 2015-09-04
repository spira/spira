<?php

/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 20.08.15
 * Time: 11:55.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\ArticleTagTransformer;
use App\Models\Article;
use Spira\Model\Model\BaseModel;
use App\Models\Tag;
use Illuminate\Http\Request;
use Spira\Responder\Response\ApiResponse;

class ArticleTagController extends ChildEntityController
{
    protected $relationName = 'tags';

    public function __construct(Article $parentModel, ArticleTagTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }

    /**
     * Put many entities.
     *
     * @param string $id
     * @param  Request $request
     * @return ApiResponse
     */
    public function putMany(Request $request, $id)
    {
        $parent = $this->findParentEntity($id);

        $requestCollection = $request->all();

        $this->validateRequestCollection($requestCollection, $this->getValidationRules());

        $existingChildModels = Tag::whereIn('tag', $this->getIds($requestCollection, 'tag'))->get();

        $childModels = $this->getChildModel()
            ->hydrateRequestCollection($requestCollection, $existingChildModels)
            ->each(function (BaseModel $model) {
                if (! $model->exists) {
                    $model->save();
                }
            });

        $this->getRelation($parent)->sync($childModels->lists('tag_id')->toArray());

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->createdCollection($childModels);
    }
}
