<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\Article;

class ArticleMetaController extends ChildEntityController
{
    protected $relationName = 'articleMeta';

    public function __construct(Article $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
