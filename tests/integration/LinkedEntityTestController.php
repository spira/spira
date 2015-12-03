<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\tests\integration;

use Spira\Core\Controllers\LinkedEntityController;
use Spira\Core\Model\Test\TestEntity;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class LinkedEntityTestController extends LinkedEntityController
{
    public function __construct(TestEntity $parentModel, EloquentModelTransformer $transformer)
    {
        $this->relationName = 'secondTestEntities';
        parent::__construct($parentModel, $transformer);
    }
}
