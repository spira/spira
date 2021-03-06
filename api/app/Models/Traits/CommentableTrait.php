<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\Traits;

use App\Models\PostDiscussion;

trait CommentableTrait
{
    /**
     * Get comment relationship.
     *
     * @return PostDiscussion
     */
    public function comments()
    {
        return (new PostDiscussion())->setPost($this);
    }
}
