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
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Exceptions\UnauthorizedException;
use App\Http\Transformers\EloquentModelTransformer;

class ArticleCommentController extends ChildEntityController
{
    /**
     * Name in the parent model of this entity.
     *
     * @var string
     */
    protected $relationName = 'comments';

    /**
     * JWT Auth.
     *
     * @var JWTAuth
     */
    protected $jwtAuth;

    /**
     * Set dependencies.
     *
     * @param  Article                  $parentModel
     * @param  EloquentModelTransformer $transformer
     * @param  JWTAuth                  $jwtAuth
     *
     * @return void
     */
    public function __construct(
        Article $parentModel,
        EloquentModelTransformer $transformer,
        JWTAuth $jwtAuth
    ) {
        $this->jwtAuth = $jwtAuth;

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
        // Add the current user to the request
        if ($user = $this->jwtAuth->user()) {
            $request->merge(['user_id' => $user->user_id]);
        } else {
            throw new UnauthorizedException('Not logged in.');
        }

        $this->validateRequest($request->all(), $this->getValidationRules());

        $parent = $this->findParentEntity($id);
        $childModel = $this->getRelation($parent);
        $childModel = $childModel->save($request->all(), $user);

        // If we respond with createdItem() it would be an empty response, so
        // we respond with item() instead to provide the data from the new
        // comment
        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($childModel);
    }
}
