<?php


use App\Models\Tag;

class ArticleSeeder extends AbstractPostSeeder
{

    protected function getClass()
    {
        return \App\Models\Article::class;
    }

    protected function getGroupTagPivots($tags)
    {
        return Tag::getGroupedTagPivots($tags, SeedTags::articleGroupTagName);
    }
}