<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 21.08.15
 * Time: 17:15.
 */

namespace App\Http\Transformers;

use App\Models\Tag;

class ArticleTagTransformer extends EloquentModelTransformer
{
    public $addSelfKey = true;

    /**
     * @param $collection
     * @return mixed
     */
    public function transformCollection($collection)
    {
        /** @var Tag[] $collection */
        foreach ($collection as $item) {
            $item->addHidden(['pivot']);
        }

        return parent::transformCollection($collection);
    }
}
