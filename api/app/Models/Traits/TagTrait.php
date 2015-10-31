<?php


namespace App\Models\Traits;


use App\Models\Tag;

trait TagTrait
{
    public function tags()
    {
        return $this->belongsToManyRevisionable(Tag::class,null,null,null,'tags')->withPivot('tag_group_id', 'tag_group_parent_id');
    }
}