<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Models\TestEntity;
use App\Extensions\Controller\LocalizableTrait;
use App\Http\Transformers\EloquentModelTransformer;

class ChildTestController extends ChildEntityController
{
    use LocalizableTrait;

    protected $relationName = 'testMany';
    protected $validateParentIdRule = 'uuid';
    protected $validateChildIdRule = 'uuid';

    public function __construct(TestEntity $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
