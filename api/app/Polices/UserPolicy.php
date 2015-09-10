<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 08.09.15
 * Time: 0:00
 */

namespace App\Polices;


use App\Models\User;
use Spira\Model\Collection\Collection;

/*
 *         $this->lock->setRole(User::$userTypes);
        $user = $this->jwtAuth->user();
        $owner = [User::class, 'userIsOwner', $user, last($request->segments())];

        $this->lock->role(User::USER_TYPE_ADMIN)->permit(['readAll', 'readOne', 'update', 'delete']);
        $this->lock->role(User::USER_TYPE_GUEST)->permit(['readOne', 'update'], [$owner]);

        $this->middleware('permission:readAll', ['only' => 'getAllPaginated']);
        $this->middleware('permission:readOne', ['only' => 'getOne']);
        $this->middleware('permission:update', ['only' => 'patchOne']);
        $this->middleware('permission:delete', ['only' => 'deleteOne']);
 */

class UserPolicy
{
    public function getOne(User $user, User $affectedUser)
    {
        if ($user->user_id === $affectedUser->user_id || $user->isAdmin() ){
            return true;
        }

        return false;
    }

    public function getAll(User $user, Collection $affectedUsers)
    {
        if ($user->isAdmin()){
            return true;
        }

        return false;
    }

    public function getAllPaginated(User $user, Collection $affectedUsers)
    {
        if ($user->isAdmin()){
            return true;
        }

        return false;
    }

    public function patchOne(User $user, User $affectedUser)
    {
        if ($affectedUser->getOriginal('user_id') === $user->user_id || $user->isAdmin()){
            return true;
        }

        return false;
    }

    public function deleteOne(User $user, User $affectedUser)
    {
        if ($user->isAdmin()){
            return true;
        }

        return false;
    }
}