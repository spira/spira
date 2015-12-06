<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Spira\Model\Model\BaseModel;
use Spira\Responder\Contract\TransformerInterface;

abstract class AbstractTagController extends ChildEntityController
{
    protected $relationName = 'tags';

    public function __construct(BaseModel $parentModel, TransformerInterface $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }

    protected function getPivotValidationRules()
    {
        return [
            '_pivot.tag_group_id' => 'required|uuid',
            '_pivot.tag_group_parent_id' => 'required|uuid',
        ];
    }
    
}
