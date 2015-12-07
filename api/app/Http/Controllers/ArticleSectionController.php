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
use Spira\Core\Controllers\LocalizableTrait;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class ArticleSectionController extends AbstractSectionController
{
    use LocalizableTrait;

    protected $relationName = 'sections';

    public function __construct(Article $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
