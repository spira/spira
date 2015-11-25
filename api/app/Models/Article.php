<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use App\Models\Scopes\ArticleScope;

/**
 * Class Article.
 */
class Article extends AbstractPost
{
    protected $attributes = [
        'post_type' => self::class,
    ];

    protected static function bootScope()
    {
        static::addGlobalScope(new ArticleScope());
    }
}
