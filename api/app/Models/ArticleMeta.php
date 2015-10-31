<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Spira\Model\Model\BaseModel;

class ArticleMeta extends Meta
{
    public $table = 'metas_article';

    /**
     * @return BaseModel
     */
    public function getParentClassName()
    {
        return Article::class;
    }
}
