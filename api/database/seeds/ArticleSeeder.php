<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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
