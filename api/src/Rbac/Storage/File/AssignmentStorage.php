<?php


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
     * @inheritdoc
     */
    public function getAssignments($userId)
    {
        return isset($this->assignments[$userId]) ? $this->assignments[$userId] : [];
    }

    /**
     * @inheritdoc
     */
    public function assign(Role $role, $userId)
    {
//        if (!isset($this->items[$role->name])) {
//            throw new InvalidParamException("Unknown role '{$role->name}'.");

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
     * @inheritdoc
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