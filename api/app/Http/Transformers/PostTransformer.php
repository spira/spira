<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Transformers;

use App\Models\AbstractPost;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class PostTransformer extends EloquentModelTransformer
{
    /**
     * @param $collection
     * @param array $options
     * @return mixed
     */
    public function transformCollection($collection, array $options = [])
    {
        /** @var AbstractPost[] $collection */
        foreach ($collection as $item) {
            $item->addHidden('content');
        }

        return parent::transformCollection($collection, $options);
    }
}
