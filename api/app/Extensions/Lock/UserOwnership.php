<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Lock;

interface UserOwnership
{
    /**
     * Check if the user is owns the entity.
     *
     * @param  \App\Models\User  $user
     * @param  string            $entityId
     * @return bool
     */
    public static function userIsOwner($user, $entityId);
}
