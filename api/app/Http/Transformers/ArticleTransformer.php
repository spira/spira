<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Transformers;

use App\Models\Article;

class ArticleTransformer extends EloquentModelTransformer
{
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
