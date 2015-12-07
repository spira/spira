<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Models\Tag;
use Spira\Core\Controllers\ChildEntityController;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class ChildTagController extends ChildEntityController
{
    protected $relationName = 'childTags';

    public function __construct(Tag $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
