<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Polices;

use App\Models\User;
use Spira\Model\Collection\Collection;

class UserPolicy
{
    public function getOne(User $user, User $affectedUser)
    {
        if ($user->user_id === $affectedUser->user_id || $user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function getAll(User $user, Collection $affectedUsers)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function getAllPaginated(User $user, Collection $affectedUsers)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function patchOne(User $user, User $affectedUser)
    {
        if ($affectedUser->getOriginal('user_id') === $user->user_id || $user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function deleteOne(User $user, User $affectedUser)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }
}
