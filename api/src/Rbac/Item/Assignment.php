<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Item;

/**
 * Assignment represents an assignment of a role to a user.
 */
class Assignment
{
    /**
     * @var string|int user ID
     */
    public $userId;
    /**
     * @return string the role name
     */
    public $roleName;
    /**
     * @var string datetime representing the assignment creation time
     */
    public $createdAt;
}
