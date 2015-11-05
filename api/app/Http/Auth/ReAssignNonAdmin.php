<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Spira\Rbac\Item\Rule;
use Spira\Rbac\User\UserProxy;

class ReAssignNonAdmin extends Rule
{
    private $adminRoles = [Role::ADMIN_ROLE, Role::SUPER_ADMIN_ROLE];

    /**
     * Executes the rule.
     *
     * @param UserProxy $userProxy
     * @param array $params parameters passed to check.
     * @return bool a value indicating whether the rule permits the auth item it is associated with.
     */
    public function execute(UserProxy $userProxy, $params)
    {
        /** @var User $user */
        $user = $params['model'];
        $userRoles = $user->roles->lists('key')->toArray();
        $newRoles = [];
        if (! empty($params['children']) && $params['children'] instanceof Collection) {
            /** @var Collection $newRolesCollection */
            $newRolesCollection = $params['children'];
            $newRoles = $newRolesCollection->lists('key')->toArray();
        }

        $detachedRoles = array_diff($newRoles, $userRoles);
        $attachedRoles = array_diff($userRoles, $newRoles);
        if (
            count(array_intersect($detachedRoles, $this->adminRoles)) > 0 ||
            count(array_intersect($attachedRoles, $this->adminRoles)) > 0
        ) {
            return false;
        }

        return true;
    }
}
