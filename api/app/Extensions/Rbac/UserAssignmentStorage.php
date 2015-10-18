<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Rbac;

use App\Models\User;
use Spira\Contract\Exception\NotImplementedException;
use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Role;
use Spira\Rbac\Storage\AssignmentStorageInterface;

class UserAssignmentStorage implements AssignmentStorageInterface
{
    /**
     * Returns all role assignment information for the specified user.
     * @param string|int $userId the user ID
     * @return Assignment[] the assignments indexed by role names. An empty array will be
     * returned if there is no role assigned to the user.
     */
    public function getAssignments($userId)
    {
        /** @var User $user */
        $user = User::findOrFail($userId);
        $assignments = [];

        /** @var \App\Models\Role $role */
        foreach ($user->roles as $role) {
            $assignment = new Assignment();
            $assignment->userId = $userId;
            $assignment->roleName = $role->role_name;
            $assignments[$role->role_name] = $assignment;
        }

        return $assignments;
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
        /** @var User $user */
        $user = User::findOrFail($userId);

        $roleModel = new \App\Models\Role();
        $roleModel->role_name = $role->name;

        $user->roles()->save($roleModel);

        $assignment = new Assignment();
        $assignment->userId = $userId;
        $assignment->roleName = $role->name;

        return $assignment;
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
        if (! $userId) {
            return false;
        }

        $role = \App\Models\Role::where('user_id', '=', $userId)->where('role_name', '=', $role->name)->first();

        if ($role && $role->delete()) {
            return true;
        }

        return false;
    }

    /**
     * @param Role $role
     * @return bool
     */
    public function removeAllAssignments(Role $role)
    {
        throw new NotImplementedException('Massive removal via Storage is disabled');
    }

    /**
     * @param $oldName
     * @param Role $role
     * @return bool
     */
    public function updateAllAssignments($oldName, Role $role)
    {
        throw new NotImplementedException('Massive update via Storage is disabled');
    }
}
