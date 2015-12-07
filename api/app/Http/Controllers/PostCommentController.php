<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostDiscussion;
use Spira\Core\Controllers\ChildEntityController;
use Spira\Core\Responder\Response\ApiResponse;

abstract class PostCommentController extends ChildEntityController
{
    /**
     * Name in the parent model of this entity.
     *
     * @var string
     */
    protected $relationName = 'comments';

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
