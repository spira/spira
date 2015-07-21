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

        $this->middleware('permission:readAll', ['only' => 'getAll']);
        $this->middleware('permission:readOne', ['only' => 'getOne']);
        $this->middleware('permission:update', ['only' => 'patchOne']);
        $this->middleware('permission:delete', ['only' => 'deleteOne']);
        $this->middleware('permission:reset', ['only' => 'resetPassword']);
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
        $user = $this->jwtAuth->user();
        $owner = [User::class, 'userIsOwner', $user, $request->segment(2)];

        $this->lock->role(User::USER_TYPE_ADMIN)->permit(['readAll', 'readOne', 'update', 'delete', 'reset']);
        $this->lock->role(User::USER_TYPE_GUEST)->permit(['readOne', 'update', 'reset'], [$owner]);
    }

    /**
     * Reset user password.
     *
     * @param string $id
     *
     * @return Response
     */
    public function resetPassword($id)
    {
        $this->repository->resetPassword($id);

        return response(null, 202);
    }
}
