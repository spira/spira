<?php

namespace App\Http\Controllers;

use App;
use App\Models\User;
use Tymon\JWTAuth\JWTAuth;
use BeatSwitch\Lock\Manager;
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
     * @return void
     */
    public function __construct(Repository $repository, Validator $validator, Manager $lock, JWTAuth $jwtAuth)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->lock = $lock;
        $this->jwtAuth = $jwtAuth;

        $this->assignPermissions();
    }

    /**
     * Assign permissions to be used in the controller.
     *
     * @return void
     */
    public function assignPermissions()
    {
        $this->lock->setRole(User::$userTypes);
        $self = App::make('App\Http\Permissions\SelfCondition');

        $this->lock->role(User::USER_TYPE_ADMIN)->allow('readAll', 'users');
        $this->lock->role(User::USER_TYPE_ADMIN)->allow('readOne', 'users');
        $this->lock->role(User::USER_TYPE_ADMIN)->allow('update', 'users');
        $this->lock->role(User::USER_TYPE_ADMIN)->allow('delete', 'users');

        $this->lock->role(User::USER_TYPE_GUEST)->allow('readOne', 'users', null, $self);
        $this->lock->role(User::USER_TYPE_GUEST)->allow('update', 'users', null, $self);
    }

    /**
     * Get all entities.
     *
     * @return Response
     */
    public function getAll()
    {
        $this->checkPermission('readAll', 'users');

        return parent::getAll();
    }

    /**
     * Get one entity.
     *
     * @param string $id
     *
     * @return Response
     */
    public function getOne($id)
    {
        $this->checkPermission('readOne', 'users');

        return parent::getOne($id);
    }

    /**
     * Patch an entity.
     *
     * @param string  $id
     * @param Request $request
     *
     * @return Response
     */
    public function patchOne($id, Request $request)
    {
        $this->checkPermission('update', 'users');

        return parent::patchOne($id, $request);
    }

    /**
     * Delete an entity.
     *
     * @param string $id
     *
     * @return Response
     */
    public function deleteOne($id)
    {
        $this->checkPermission('delete', 'users');

        return parent::deleteOne($id);
    }
}
