<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Model\Model;

abstract class VirtualModel extends BaseModel
{
    public $table = false;

    protected $primaryKey = false;

    public $timestamps = false;

    public function save($options = [])
    {
        throw new \LogicException('Cannot save virtual model');
    }

    public function getKey()
    {
        if (! $this->getKeyName()) {
            throw new \InvalidArgumentException("Virtual model doesn't have a primary key");
        }

        return parent::getKey();
    }
}
