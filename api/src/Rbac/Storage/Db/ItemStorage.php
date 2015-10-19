<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Storage\Db;

use Spira\Rbac\Item\Item;
use Spira\Rbac\Item\Permission;
use Spira\Rbac\Item\Role;
use Spira\Rbac\Storage\ItemStorageInterface;

class ItemStorage extends AbstractStorage implements ItemStorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItem($itemName)
    {
        if (empty($itemName)) {
            return;
        }

        $row = $this->getConnection()
            ->table('auth_item')
            ->where('name', '=', $itemName)
            ->first();

        if (! $row) {
            return;
        }

        return $this->populateItem($row);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentNames($itemName)
    {
        $rows = $this->getConnection()
            ->table('auth_item_child')
            ->select(['parent'])
            ->where('child', '=', $itemName)
            ->get();

        $parents = [];
        foreach ($rows as $row) {
            $parents[] = $row->parent;
        }

        return $parents;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($name)
    {
        $rows = $this->getConnection()
            ->table('auth_item')
            ->join('auth_item_child', 'auth_item.name', '=', 'auth_item_child.child')
            ->select(['name', 'type', 'description', 'rule_name', 'created_at', 'updated_at'])
            ->where('parent', '=', $name)
            ->get();

        $children = [];
        foreach ($rows as $row) {
            $children[$row->name] = $this->populateItem($row);
        }

        return $children;
    }

    /**
     * {@inheritdoc}
     */
    public function addItem(Item $item)
    {
        $this->getConnection()
            ->table('auth_item')
            ->insert([
                'name' => $item->name,
                'type' => $item->type,
                'description' => $item->description,
                'rule_name' => $item->getRuleName(),
                'created_at' => 'now()',
                'updated_at' => 'now()',
            ]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeItem(Item $item)
    {
        $this->getConnection()
            ->table('auth_item')
            ->where('name', '=', $item->name)
            ->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateItem($name, Item $item)
    {
        $this->getConnection()
            ->table('auth_item')
            ->where('name', '=', $name)
            ->update([
                'name' => $item->name,
                'description' => $item->description,
                'rule_name' => $item->getRuleName(),
                'updated_at' => 'now()',
            ]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(Item $parent, Item $child)
    {
        if ($this->detectLoop($parent, $child)) {
            throw new \InvalidArgumentException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
        }

        $this->getConnection()
            ->table('auth_item_child')
            ->insert(['parent' => $parent->name, 'child' => $child->name]);

        return true;
    }

    /**
     * Checks whether there is a loop in the authorization item hierarchy.
     * @param Item $parent the parent item
     * @param Item $child the child item to be added to the hierarchy
     * @return bool whether a loop exists
     */
    protected function detectLoop($parent, $child)
    {
        if ($child->name === $parent->name) {
            return true;
        }
        foreach ($this->getChildren($child->name) as $grandchild) {
            if ($this->detectLoop($parent, $grandchild)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Populates an auth item with the data fetched from database.
     * @param array $row the data from the auth item table
     * @return Item the populated auth item instance (either Role or Permission)
     */
    protected function populateItem($row)
    {
        $class = $row->type == Item::TYPE_PERMISSION ? Permission::class : Role::class;

        $object = new $class($row->name);
        $object->type = $row->type;
        $object->description = $row->description;
        $object->ruleName = $row->rule_name;
        $object->createdAt = $row->created_at;
        $object->updatedAt = $row->updated_at;

        return $object;
    }
}
