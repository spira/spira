<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Storage\File;

use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Role;
use Spira\Rbac\Storage\AssignmentStorageInterface;

class AssignmentStorage extends AbstractStorage implements AssignmentStorageInterface
{
    /**
     * @var Assignment[]
     */
    protected $assignments;

    /**
     * {@inheritdoc}
     */
    public function getAssignments($userId)
    {
        return isset($this->assignments[$userId]) ? $this->assignments[$userId] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function assign(Role $role, $userId)
    {
        if (isset($this->assignments[$userId][$role->name])) {
            throw new \InvalidArgumentException("Authorization item '{$role->name}' has already been assigned to user '$userId'.");
        } else {
            $assignmentObj = new Assignment();
            $assignmentObj->userId = $userId;
            $assignmentObj->roleName = $role->name;
            $assignmentObj->createdAt = time();
            $this->assignments[$userId][$role->name] = $assignmentObj;

            $this->saveAssignments();

            return $this->assignments[$userId][$role->name];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function revoke(Role $role, $userId)
    {
        if (isset($this->assignments[$userId][$role->name])) {
            unset($this->assignments[$userId][$role->name]);
            $this->saveAssignments();

            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeAllAssignments(Role $role)
    {
        foreach ($this->assignments as &$assignments) {
            unset($assignments[$role->name]);
        }

        $this->saveAssignments();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateAllAssignments($oldName, Role $role)
    {
        foreach ($this->assignments as &$assignments) {
            if (isset($assignments[$oldName])) {
                $assignments[$role->name] = $assignments[$oldName];
                unset($assignments[$oldName]);
            }
        }

        $this->saveAssignments();

        return true;
    }

    /**
     * Loads authorization data from persistent storage.
     */
    protected function load()
    {
        $this->assignments = [];
        $assignments = $this->loadFromFile($this->filePath);
        $assignmentsMtime = @filemtime($this->filePath);

        foreach ($assignments as $userId => $roles) {
            foreach ($roles as $role) {
                $assignmentObj = new Assignment();
                $assignmentObj->userId = $userId;
                $assignmentObj->roleName = $role;
                $assignmentObj->createdAt = $assignmentsMtime;
                $this->assignments[$userId][$role] = $assignmentObj;
            }
        }
    }

    /**
     * Saves assignments data into persistent storage.
     */
    protected function saveAssignments()
    {
        $assignmentData = [];
        foreach ($this->assignments as $userId => $assignments) {
            foreach ($assignments as $name => $assignment) {
                /* @var $assignment Assignment */
                $assignmentData[$userId][] = $assignment->roleName;
            }
        }
        $this->saveToFile($assignmentData, $this->filePath);
    }
}
