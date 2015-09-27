<?php

namespace Spira\Rbac\Storage;

use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Item;


interface StorageInterface
{

    /**
     * Returns all role assignment information for the specified user.
     * @param string|integer $userId the user ID
     * @return Assignment[] the assignments indexed by role names. An empty array will be
     * returned if there is no role assigned to the user.
     */
    public function getAssignments($userId);

    /**
     * @param string $itemName
     * @return Item
     */
    public function getItem($itemName);

    /**
     * @param string $itemName
     * @return Item[]
     */
    public function getParentNames($itemName);


}
