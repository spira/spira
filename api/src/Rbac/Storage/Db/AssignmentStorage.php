<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Storage\Db;

use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Role;
use Spira\Rbac\Storage\AssignmentStorageInterface;

class AssignmentStorage extends AbstractStorage implements AssignmentStorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAssignments($userId)
    {
        if (empty($userId)) {
            return [];
        }

        $data = $this->getConnection()
            ->table('auth_assignment')
            ->where('user_id', '=', $userId)
            ->get();

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
     * {@inheritdoc}
     */
    public function assign(Role $role, $userId)
    {
        $assignment = new Assignment();
        $assignment->userId = $userId;
        $assignment->roleName = $role->name;
        $this->getConnection()
            ->table('auth_assignment')
            ->insert(['user_id' => $assignment->userId, 'item_name' => $assignment->roleName, 'created_at' => 'now()']);

        return $assignment;
    }

    /**
     * {@inheritdoc}
     */
    public function revoke(Role $role, $userId)
    {
        if (empty($userId)) {
            return false;
        }

        $this->getConnection()
            ->table('auth_assignment')
            ->where('user_id', '=', (string) $userId)
            ->where('item_name', '=', $role->name)
            ->delete();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllAssignments(Role $role)
    {
        $this->getConnection()
            ->table('auth_assignment')
            ->where('item_name', '=', $role->name)
            ->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function updateAllAssignments($oldName, Role $role)
    {
        $this->getConnection()
            ->table('auth_assignment')
            ->where('item_name', '=', $oldName)
            ->update(['item_name' => $role->name]);
    }
}
