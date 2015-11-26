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
use Spira\Responder\Response\ApiResponse;

class TagController extends EntityController
{
    public function __construct(Tag $model, EloquentModelTransformer $transformer)
    {
        parent::__construct($model, $transformer);
    }

    /**
     * Get tags of a particular type.
     *
     * @param  string $group
     * @return ApiResponse
     */
    public function getGroupTags($group)
    {
        /** @var Tag $tagGroupTag */
        $tagGroupTag = Tag::where('tag', '=', urldecode($group))->firstOrFail();

        $groupTags = $tagGroupTag->childTags()->get();

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($groupTags);
    }
}
