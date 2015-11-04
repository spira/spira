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
use Spira\Rbac\Item\Rule;
use Spira\Rbac\User\UserProxy;

class ImpersonateNonAdmin extends Rule
{
    /**
     * Executes the rule.
     *
     * @param UserProxy $userProxy
     * @param User $targetUser
     * @return bool a value indicating whether the rule permits the auth item it is associated with.
     * @internal param array $params parameters passed to check.
     */
    public function execute(UserProxy $userProxy, $targetUser)
    {
        $roles = $targetUser->roles()->get()->pluck('key')->toArray();

        if (! in_array(Role::ADMIN_ROLE, $roles)) {
            return true;
        }

        return false;
    }
}
