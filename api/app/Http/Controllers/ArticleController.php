<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Transformers\ArticleTransformer;
use App\Extensions\Controller\LocalizableTrait;
use App\Http\Controllers\Traits\TagCategoryTrait;

class ArticleController extends EntityController
{
    use LocalizableTrait;

    use TagCategoryTrait;
    protected $rootCategoryTagName = \SeedTags::articleGroupTagName;

    /**
     * Assign dependencies.
     * @param Article $model
     * @param ArticleTransformer $transformer
     */
    public function __construct(Article $model, ArticleTransformer $transformer)
    {
        parent::__construct($model, $transformer);
    }
}
