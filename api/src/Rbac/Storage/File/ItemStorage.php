<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Storage\File;

use Spira\Rbac\Item\Item;
use Spira\Rbac\Item\Permission;
use Spira\Rbac\Item\Role;
use Spira\Rbac\Storage\ItemStorageInterface;

class ItemStorage extends AbstractStorage implements ItemStorageInterface
{
    /**
     * @var Item[]
     */
    protected $items;

    /**
     * @var Item[]
     */
    protected $children;

    /**
     * {@inheritdoc}
     */
    public function getItem($itemName)
    {
        return isset($this->items[$itemName]) ? $this->items[$itemName] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($type)
    {
        $items = [];
        foreach ($this->items as $item) {
            if ($item->type === $type){
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentNames($itemName)
    {
        $parentNames = [];
        foreach ($this->children as $parentName => $children) {
            if (isset($children[$itemName])) {
                $parentNames[] = $parentName;
            }
        }

        return $parentNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($name)
    {
        return isset($this->children[$name]) ? $this->children[$name] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function addItem(Item $item)
    {
        $time = time();
        if ($item->createdAt === null) {
            $item->createdAt = $time;
        }
        if ($item->updatedAt === null) {
            $item->updatedAt = $time;
        }

        $this->items[$item->name] = $item;

        $this->saveItems();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeItem(Item $item)
    {
        if (isset($this->items[$item->name])) {
            foreach ($this->children as &$children) {
                unset($children[$item->name]);
            }

            unset($this->items[$item->name]);
            $this->saveItems();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateItem($name, Item $item)
    {
        if ($name !== $item->name) {
            if (isset($this->items[$item->name])) {
                throw new \InvalidArgumentException("Unable to change the item name. The name '{$item->name}' is already used by another item.");
            } else {
                // Remove old item in case of renaming
                unset($this->items[$name]);

                if (isset($this->children[$name])) {
                    $this->children[$item->name] = $this->children[$name];
                    unset($this->children[$name]);
                }
                foreach ($this->children as &$children) {
                    if (isset($children[$name])) {
                        $children[$item->name] = $children[$name];
                        unset($children[$name]);
                    }
                }
            }
        }

        $this->items[$item->name] = $item;

        $this->saveItems();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(Item $parent, Item $child)
    {
        if (! isset($this->items[$parent->name], $this->items[$child->name])) {
            throw new \InvalidArgumentException("Either '{$parent->name}' or '{$child->name}' does not exist.");
        }

        if ($this->detectLoop($parent, $child)) {
            throw new \InvalidArgumentException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
        }

        if (isset($this->children[$parent->name][$child->name])) {
            throw new \InvalidArgumentException("The item '{$parent->name}' already has a child '{$child->name}'.");
        }

        $this->children[$parent->name][$child->name] = $this->items[$child->name];
        $this->saveItems();

        return true;
    }

    /**
     * Loads authorization data from persistent storage.
     */
    protected function load()
    {
        $this->children = [];
        $this->items = [];

        $items = $this->loadFromFile($this->filePath);
        $itemsMtime = @filemtime($this->filePath);

        foreach ($items as $name => $item) {
            $class = $item['type'] == Item::TYPE_PERMISSION ? Permission::class : Role::class;
            $itemObj = new $class($name);
            $itemObj->description = isset($item['description']) ? $item['description'] : null;
            $itemObj->ruleName = isset($item['ruleName']) ? $item['ruleName'] : null;
            $itemObj->createdAt = $itemsMtime;
            $itemObj->updatedAt = $itemsMtime;
            $this->items[$name] = $itemObj;
        }

        foreach ($items as $name => $item) {
            if (isset($item['children'])) {
                foreach ($item['children'] as $childName) {
                    if (isset($this->items[$childName])) {
                        $this->children[$name][$childName] = $this->items[$childName];
                    }
                }
            }
        }
    }

    /**
     * Saves items data into persistent storage.
     */
    protected function saveItems()
    {
        $items = [];
        foreach ($this->items as $name => $item) {
            /* @var $item Item */
            $items[$name] = array_filter(
                [
                    'type' => $item->type,
                    'description' => $item->description,
                    'ruleName' => $item->ruleName,
                ]
            );
            if (isset($this->children[$name])) {
                foreach ($this->children[$name] as $child) {
                    /* @var $child Item */
                    $items[$name]['children'][] = $child->name;
                }
            }
        }
        $this->saveToFile($items, $this->filePath);
    }

    /**
     * Checks whether there is a loop in the authorization item hierarchy.
     *
     * @param Item $parent parent item
     * @param Item $child the child item that is to be added to the hierarchy
     * @return bool whether a loop exists
     */
    protected function detectLoop($parent, $child)
    {
        if ($child->name === $parent->name) {
            return true;
        }
        if (! isset($this->children[$child->name], $this->items[$parent->name])) {
            return false;
        }
        foreach ($this->children[$child->name] as $grandchild) {
            /* @var $grandchild Item */
            if ($this->detectLoop($parent, $grandchild)) {
                return true;
            }
        }

        return false;
    }
}
