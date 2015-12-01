<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Spira\Core\Controllers\EntityController;
use App\Http\Transformers\PostTransformer;
use App\Models\Article;
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
     * @param PostTransformer $transformer
     */
    public function __construct(Article $model, PostTransformer $transformer)
    {
        parent::__construct($model, $transformer);
    }
}
