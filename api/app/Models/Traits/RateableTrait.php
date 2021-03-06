<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\Traits;

use App\Models\Rating;
use Spira\Core\Model\Model\BaseModel;

trait RateableTrait
{
    public function userRatings()
    {
        /* @var BaseModel $this */
        return $this->morphMany(Rating::class, 'rateable');
    }
}
