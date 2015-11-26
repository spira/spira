<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\Traits;

use App\Models\Tag;

trait TagTrait
{
    public function tags()
    {
        $tagTable = null;
        $tagFk = null;
        if (property_exists($this, 'tagTable')) {
            $tagTable = $this->tagTable;
        }

        if (property_exists($this, 'tagFk')) {
            $tagFk = $this->tagFk;
        }

        return $this->belongsToManyRevisionable(Tag::class, $tagTable, $tagFk, null, 'tags')->withPivot('tag_group_id', 'tag_group_parent_id');
    }
}
