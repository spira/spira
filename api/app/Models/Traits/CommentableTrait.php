<?php

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
