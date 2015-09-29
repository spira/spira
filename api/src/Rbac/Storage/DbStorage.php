<?php

namespace Spira\Rbac\Storage;



use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Item;
use Spira\Rbac\Item\Permission;
use Spira\Rbac\Item\Role;

class DbStorage implements StorageInterface
{
    /**
     * @var ConnectionResolverInterface
     */
    private $connection;

    public function __construct(ConnectionResolverInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns all role assignment information for the specified user.
     * @param string|integer $userId the user ID
     * @return Assignment[] the assignments indexed by role names. An empty array will be
     * returned if there is no role assigned to the user.
     */
    public function getAssignments($userId)
    {
        if (empty($userId)) {
            return [];
        }

        $data = $this->getConnection()->select(
            'select * from auth_assignment WHERE user_id = ?', [$userId]
        );

        $assignments = [];
        foreach ($data as $row) {
            $assignment = new Assignment();
            $assignment->userId = $row->user_id;
            $assignment->roleName = $row->item_name;
            $assignment->createdAt = $row->created_at;
            $assignments[$row->item_name] = $assignment;
        }

        return $assignments;
    }

    /**
     * @param string $itemName
     * @return Item
     */
    public function getItem($itemName)
    {
        if (empty($itemName)) {
            return null;
        }

        $row = $this->getConnection()->selectOne(
            'select * from auth_item where name = ?', [$itemName]
        );

        if (!$row) {
            return null;
        }

        return $this->populateItem($row);;
    }

    /**
     * @param string $itemName
     * @return array name of the parents of the item
     */
    public function getParentNames($itemName)
    {
        $rows = $this->getConnection()->select(
            'select parent from auth_item_child where child = ?', [$itemName]
        );

        $parents = [];
        foreach ($rows as $row) {
            $parents[] = $row->parent;
        }


        return $parents;
    }

    public function getChildren($name)
    {
        $rows = $this->getConnection()->select(
            'select name, type, description, rule_name, created_at, updated_at from auth_item, auth_item_child
             WHERE parent = ? and name = child',
            [$name]
        );

        $children = [];
        foreach ($rows as $row) {
            $children[$row->name] = $this->populateItem($row);
        }

        return $children;
    }


    public function addItem(Item $item)
    {
        $this->getConnection()->insert(
            'insert into auth_item (name, type, description, rule_name, created_at, updated_at) VALUES (?,?,?,?,now(),now())',
            [$item->name, $item->type, $item->description, $item->getRuleName()]
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeItem(Item $item)
    {
        $this->getConnection()->delete(
            'delete from auth_item WHERE name = ?', [$item->name]
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function updateItem($name, Item $item)
    {

        $this->getConnection()->update(
            'update auth_item set name = ?, description = ?, rule_name = ?, updated_at = now() where name = ?',
            [$item->name, $item->description, $item->getRuleName(), $name]
        );

        return true;
    }

    public function addChild(Item $parent,Item $child)
    {
        if ($parent->name === $child->name) {
            throw new \InvalidArgumentException("Cannot add '{$parent->name}' as a child of itself.");
        }

        if ($parent instanceof Permission && $child instanceof Role) {
            throw new \InvalidArgumentException("Cannot add a role as a child of a permission.");
        }

        if ($this->detectLoop($parent, $child)) {
            throw new \InvalidArgumentException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
        }

        $this->getConnection()->insert(
            'insert into auth_item_child (parent, child) VALUES (?, ?)',
            [$parent->name, $child->name]
        );

        return true;
    }

    public function assign(Role $role, $userId)
    {
        $assignment = new Assignment();
        $assignment->userId = $userId;
        $assignment->roleName = $role->name;
        $this->getConnection()->insert(
            'insert into auth_assignment (user_id, item_name, created_at) VALUES (?, ?, now())',
            [$assignment->userId, $assignment->roleName]
        );

        return $assignment;
    }

    public function revoke(Role $role, $userId)
    {
        if (empty($userId)) {
            return false;
        }

        $this->getConnection()->delete(
            'delete from auth_assignment WHERE user_id = ? and item_name = ?',
            [(string) $userId, $role->name]
        );
    }

    /**
     * Checks whether there is a loop in the authorization item hierarchy.
     * @param Item $parent the parent item
     * @param Item $child the child item to be added to the hierarchy
     * @return boolean whether a loop exists
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
     * Populates an auth item with the data fetched from database
     * @param array $row the data from the auth item table
     * @return Item the populated auth item instance (either Role or Permission)
     */
    protected function populateItem($row)
    {
        $class = $row->type == Item::TYPE_PERMISSION ? Permission::class : Role::class;

        $object = new $class($row->name);
        $object->type=$row->type;
        $object->description=$row->description;
        $object->ruleName = $row->rule_name;
        $object->createdAt = $row->created_at;
        $object->updatedAt = $row->updated_at;

        return $object;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection->connection();
    }
}