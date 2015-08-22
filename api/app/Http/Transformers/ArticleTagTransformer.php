<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 21.08.15
 * Time: 17:15
 */

namespace App\Http\Transformers;

use App\Models\Tag;

class ArticleTagTransformer extends EloquentModelTransformer
{
    public $addSelfKey = false;

    /**
     * @param $collection
     * @return mixed
     */
    public function transformCollection($collection)
    {
        /** @var Tag[] $collection */
        foreach ($collection as $item) {
            $item->addHidden(['tag_id','pivot']);
        }

        return parent::transformCollection($collection);
    }

}