<?php

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