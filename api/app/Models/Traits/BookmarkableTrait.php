<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\Traits;

use App\Models\Bookmark;
use Spira\Model\Model\BaseModel;

trait BookmarkableTrait
{
    public function bookmarks()
    {
        /* @var BaseModel $this */
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }
}
