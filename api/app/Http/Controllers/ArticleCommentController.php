<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\PostDiscussion;
use Illuminate\Http\Request;
use Spira\Core\Controllers\ChildEntityController;
use Spira\Core\Responder\Response\ApiResponse;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class ArticleCommentController extends ChildEntityController
{
    /**
     * Name in the parent model of this entity.
     *
     * @var string
     */
    protected $relationName = 'comments';

    /**
     * Set dependencies.
     *
     * @param  Article $parentModel
     * @param  EloquentModelTransformer $transformer
     */
    public function __construct(Article $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }

    /**
     * Post a new comment.
     *
     * @param  Request $request
     * @param  string  $id
     *
     * @return ApiResponse
     */
    public function postOne(Request $request, $id)
    {
        $this->validateRequest($request->json()->all(), $this->getValidationRules($id));

        $parent = $this->findParentEntity($id);
        $childModel = $this->getRelation($parent);
        /** @var PostDiscussion $childModel */
        $childModel = $childModel->save($request->json()->all(), $request->user());

        // If we respond with createdItem() it would be an empty response, so
        // we respond with item() instead to provide the data from the new
        // comment
        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($childModel);
    }
}
