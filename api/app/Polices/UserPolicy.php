<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 08.09.15
 * Time: 0:00
 */

namespace App\Polices;


use App\Models\User;

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

    }

    public function getAll(User $user)
    {

    }

    public function getAllPaginated(User $user)
    {

    }
}