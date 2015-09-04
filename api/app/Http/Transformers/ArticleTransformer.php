<?php

namespace App\Http\Transformers;

use App\Models\Article;

class ArticleTransformer extends EloquentModelTransformer
{
    public $nestedMap = [
        'tags' => ArticleTagTransformer::class,
    ];

    /**
     * @param $collection
     * @return mixed
     */
    public function transformCollection($collection)
    {
        /** @var Article[] $collection */
        foreach ($collection as $item) {
            $item->addHidden('content');
        }

        return parent::transformCollection($collection);
    }
}
