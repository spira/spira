<?php

namespace Spira\Rbac\Item;


/**
 * Assignment represents an assignment of a role to a user.
 */
class Assignment
{
    /**
     * @var string|integer user ID
     */
    public $userId;
    /**
     * @return string the role name
     */
    public $roleName;
    /**
     * @var integer UNIX timestamp representing the assignment creation time
     */
    public $createdAt;
}
