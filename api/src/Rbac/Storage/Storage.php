<?php


namespace Spira\Rbac\Storage;


use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Item;
use Spira\Rbac\Item\Role;

class Storage implements StorageInterface
{
    /**
     * @var ItemStorageInterface
     */
    private $itemStorage;
    /**
     * @var AssignmentStorageInterface
     */
    private $assignmentStorage;

    public function __construct(ItemStorageInterface $itemStorage, AssignmentStorageInterface $assignmentStorage)
    {

        $this->itemStorage = $itemStorage;
        $this->assignmentStorage = $assignmentStorage;
    }

    /**
     * Returns all role assignment information for the specified user.
     * @param string|int $userId the user ID
     * @return Assignment[] the assignments indexed by role names. An empty array will be
     * returned if there is no role assigned to the user.
     */
    public function getAssignments($userId)
    {
        return $this->assignmentStorage->getAssignments($userId);
    }

    /**
     * Assigns a role to a user.
     *
     * @param Role $role
     * @param string|int $userId the user ID
     * @return Assignment the role assignment information.
     */
    public function assign(Role $role, $userId)
    {
        return $this->assignmentStorage->assign($role, $userId);
    }

    /**
     * Revokes a role from a user.
     *
     * @param Role $role
     * @param string|int $userId the user ID
     * @return bool whether the revoking is successful
     */
    public function revoke(Role $role, $userId)
    {
        return $this->assignmentStorage->revoke($role, $userId);
    }

    /**
     * Returns the named auth item.
     * @param string $itemName the auth item name.
     * @return Item the auth item corresponding to the specified name. Null is returned if no such item.
     */
    public function getItem($itemName)
    {
        return $this->itemStorage->getItem($itemName);
    }

    /**
     * Get names of the item's parents.
     *
     * @param string $itemName
     * @return array name of the parents of the item
     */
    public function getParentNames($itemName)
    {
        return $this->itemStorage->getParentNames($itemName);
    }

    /**
     * Returns the child permissions and/or roles.
     *
     * @param string $name the parent name
     * @return Item[] the child permissions and/or roles
     */
    public function getChildren($name)
    {
        return $this->itemStorage->getChildren($name);
    }

    /**
     * Adds an auth item to the RBAC system.
     * @param Item $item the item to add
     * @return bool whether the auth item is successfully added to the system
     */
    public function addItem(Item $item)
    {
        return $this->itemStorage->addItem($item);
    }

    /**
     * Removes an auth item from the RBAC system.
     * @param Item $item the item to remove
     * @return bool whether the role or permission is successfully removed
     */
    public function removeItem(Item $item)
    {
        return $this->itemStorage->removeItem($item);
    }

    /**
     * Updates an auth item in the RBAC system.
     * @param string $name the name of the item being updated
     * @param Item $item the updated item
     * @return bool whether the auth item is successfully updated
     */
    public function updateItem($name, Item $item)
    {
        return $this->itemStorage->updateItem($name, $item);
    }

    /**
     * Adds an item as a child of another item.
     *
     * @param Item $parent
     * @param Item $child
     */
    public function addChild(Item $parent, Item $child)
    {
        return $this->itemStorage->addChild($parent, $child);
    }
}