<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Storage;

use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Role;

interface AssignmentStorageInterface
{
    /**
     * Returns all role assignment information for the specified user.
     * @param string|int $userId the user ID
     * @return Assignment[] the assignments indexed by role names. An empty array will be
     * returned if there is no role assigned to the user.
     */
    public function getAssignments($userId);

    /**
     * Assigns a role to a user.
     *
     * @param Role $role
     * @param string|int $userId the user ID
     * @return Assignment the role assignment information.
     */
    public function assign(Role $role, $userId);

    /**
     * Revokes a role from a user.
     *
     * @param Role $role
     * @param string|int $userId the user ID
     * @return bool whether the revoking is successful
     */
    public function revoke(Role $role, $userId);

    /**
     * @param Role $role
     * @return bool
     */
    public function removeAllAssignments(Role $role);

    /**
     * @param $oldName
     * @param Role $role
     * @return bool
     */
    public function updateAllAssignments($oldName, Role $role);
}
