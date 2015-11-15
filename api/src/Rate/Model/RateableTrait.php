<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rate\Model;

use Spira\Model\Model\BaseModel;

trait RateableTrait
{
    public function rate()
    {
        /** @var BaseModel $model */
        $model = $this;

        return $model->morphMany(Rating::class, 'rateable');
    }
}
