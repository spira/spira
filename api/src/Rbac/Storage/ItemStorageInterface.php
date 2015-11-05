<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Storage;

use Spira\Rbac\Item\Item;

interface ItemStorageInterface
{
    /**
     * Returns the named auth item.
     * @param string $itemName the auth item name.
     * @return Item the auth item corresponding to the specified name. Null is returned if no such item.
     */
    public function getItem($itemName);

    /**
     * Returns all the items of the same type.
     * @param string $type the auth item type (role/permission).
     * @return Item[]
     */
    public function getItems($type);

    /**
     * Get names of the item's parents.
     *
     * @param string $itemName
     * @return array name of the parents of the item
     */
    public function getParentNames($itemName);

    /**
     * Returns the child permissions and/or roles.
     *
     * @param string $name the parent name
     * @return Item[] the child permissions and/or roles
     */
    public function getChildren($name);

    /**
     * Adds an auth item to the RBAC system.
     * @param Item $item the item to add
     * @return bool whether the auth item is successfully added to the system
     */
    public function addItem(Item $item);

    /**
     * Removes an auth item from the RBAC system.
     * @param Item $item the item to remove
     * @return bool whether the role or permission is successfully removed
     */
    public function removeItem(Item $item);

    /**
     * Updates an auth item in the RBAC system.
     * @param string $name the name of the item being updated
     * @param Item $item the updated item
     * @return bool whether the auth item is successfully updated
     */
    public function updateItem($name, Item $item);

    /**
     * Adds an item as a child of another item.
     *
     * @param Item $parent
     * @param Item $child
     */
    public function addChild(Item $parent, Item $child);
}
