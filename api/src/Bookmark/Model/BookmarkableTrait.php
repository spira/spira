<?php


namespace Spira\Bookmark\Model;

use Spira\Model\Model\BaseModel;

trait BookmarkableTrait
{
    public function bookmark()
    {
        /** @var BaseModel $model */
        $model = $this;
        return $model->morphMany(Bookmark::class, 'bookmarkable');
    }
}