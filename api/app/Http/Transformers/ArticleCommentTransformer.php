<?php

namespace App\Http\Transformers;

class ArticleCommentTransformer extends EloquentModelTransformer
{
    // @todo add handling for the format of data returned from Vanilla.
    public function transformCollection($collection)
    {
        return $collection;
    }
}
