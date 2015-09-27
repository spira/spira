<?php

namespace Spira\Rbac\Storage;


use Illuminate\Database\ConnectionInterface;
use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Item;
use Spira\Rbac\Item\Permission;
use Spira\Rbac\Item\Role;

class DbStorage implements StorageInterface
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    public function __construct(ConnectionInterface $connection)
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
            $assignments[$row['item_name']] = new Assignment([
                'userId' => $row['user_id'],
                'roleName' => $row['item_name'],
                'createdAt' => $row['created_at'],
            ]);
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
        return $this->getConnection()->select(
            'select parent from auth_item_child where child = ?', [$itemName]
        );
    }

    /**
     * Populates an auth item with the data fetched from database
     * @param array $row the data from the auth item table
     * @return Item the populated auth item instance (either Role or Permission)
     */
    protected function populateItem($row)
    {
        $class = $row['type'] == Item::TYPE_PERMISSION ? Permission::class : Role::class;

        return new $class([
            'name' => $row['name'],
            'type' => $row['type'],
            'description' => $row['description'],
            'ruleName' => $row['rule_name'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ]);
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }
}