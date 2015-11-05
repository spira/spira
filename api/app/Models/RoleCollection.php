<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Spira\Model\Collection\Collection;
use Spira\Model\Model\BaseModel;

class RoleCollection extends Collection
{
    public function load($relations)
    {
        /** @var BaseModel $item */
        foreach ($this->items as $item) {
            foreach ($relations as $relation) {
                $item->$relation;
            }
        }
    }
}
