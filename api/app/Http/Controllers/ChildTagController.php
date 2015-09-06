<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\Tag;
use Illuminate\Http\Request;
use Spira\Model\Model\BaseModel;
use Spira\Responder\Response\ApiResponse;

class ChildTagController extends ChildEntityController
{
    protected $relationName = 'childTags';

    public function __construct(Tag $parentModel, EloquentModelTransformer $transformer)
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
