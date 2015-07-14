<?php

namespace App\Http\Controllers;

use App;
use App\Models\User;
use Tymon\JWTAuth\JWTAuth;
use App\Extensions\Lock\Manager;
use Illuminate\Http\Request;
use App\Repositories\UserRepository as Repository;
use App\Http\Validators\UserValidator as Validator;

class UserController extends BaseController
{
    /**
     * Assign dependencies.
     *
     * @param  Repository  $repository
     * @param  Validator   $validator
     * @param  Lock        $lock
     * @param  JWTAuth     $jwtAuth
     * @param  Request     $request
     * @return void
     */
    public function __construct(Repository $repository, Validator $validator, Manager $lock, JWTAuth $jwtAuth, Request $request)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->lock = $lock;
        $this->jwtAuth = $jwtAuth;

        $this->assignPermissions($request);

        $this->middleware('permission:readAll,users', ['only' => 'getAll']);
        $this->middleware('permission:readOne,users', ['only' => 'getOne']);
        $this->middleware('permission:update,users', ['only' => 'patchOne']);
        $this->middleware('permission:delete,users', ['only' => 'deleteOne']);
    }

    /**
     * Assign permissions to be used in the controller.
     *
     * @param  Request  $request
     * @return void
     */
    public function assignPermissions(Request $request)
    {
        $this->lock->setRole(User::$userTypes);

        try {
            $user = $this->jwtAuth->getUser();
        } catch (\Exception $e) {
            $user = null;
        }

        $owner = [User::class, 'userIsOwner', $user, last($request->segments())];

        $this->lock->role(User::USER_TYPE_ADMIN)->allow('readAll', 'users');
        $this->lock->role(User::USER_TYPE_ADMIN)->allow('readOne', 'users');
        $this->lock->role(User::USER_TYPE_ADMIN)->allow('update', 'users');
        $this->lock->role(User::USER_TYPE_ADMIN)->allow('delete', 'users');

        $this->lock->role(User::USER_TYPE_GUEST)->allow('readOne', 'users', null, [$owner]);
        $this->lock->role(User::USER_TYPE_GUEST)->allow('update', 'users', null, [$owner]);
    }
}
