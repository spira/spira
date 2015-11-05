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
use Spira\Rbac\Item\Permission;
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
     * {@inheritdoc}
     */
    public function getAssignments($userId)
    {
        return $this->assignmentStorage->getAssignments($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function assign(Role $role, $userId)
    {
        if (! $this->itemStorage->getItem($role->name)) {
            throw new \InvalidArgumentException("Unknown role '{$role->name}'.");
        }

        return $this->assignmentStorage->assign($role, $userId);
    }

    /**
     * {@inheritdoc}
     */
    public function revoke(Role $role, $userId)
    {
        return $this->assignmentStorage->revoke($role, $userId);
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($itemName)
    {
        return $this->itemStorage->getItem($itemName);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($type)
    {
        return $this->itemStorage->getItems($type);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentNames($itemName)
    {
        return $this->itemStorage->getParentNames($itemName);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($name)
    {
        return $this->itemStorage->getChildren($name);
    }

    /**
     * {@inheritdoc}
     */
    public function addItem(Item $item)
    {
        return $this->itemStorage->addItem($item);
    }

    /**
     * {@inheritdoc}
     */
    public function removeItem(Item $item)
    {
        $result = $this->itemStorage->removeItem($item);
        if ($result && $item instanceof Role) {
            $this->removeAllAssignments($item);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function updateItem($name, Item $item)
    {
        $result = $this->itemStorage->updateItem($name, $item);

        if ($name !== $item->name && $item instanceof Role) {
            $this->updateAllAssignments($name, $item);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(Item $parent, Item $child)
    {
        if ($parent->name == $child->name) {
            throw new \InvalidArgumentException("Cannot add '{$parent->name} ' as a child of itself.");
        }
        if ($parent instanceof Permission && $child instanceof Role) {
            throw new \InvalidArgumentException('Cannot add a role as a child of a permission.');
        }

        return $this->itemStorage->addChild($parent, $child);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllAssignments(Role $role)
    {
        return $this->assignmentStorage->removeAllAssignments($role);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAllAssignments($oldName, Role $role)
    {
        return $this->assignmentStorage->updateAllAssignments($oldName, $role);
    }
}
