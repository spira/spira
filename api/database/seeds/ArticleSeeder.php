<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Tag;
use App\Models\Article;

class ArticleSeeder extends AbstractPostSeeder
{
    protected $addComments = true;

    protected function getClass()
    {
        return Article::class;
    }

    protected function getGroupTagPivots($tags)
    {
        return Tag::getGroupedTagPivots($tags, SeedTags::articleGroupTagName);
    }
}
