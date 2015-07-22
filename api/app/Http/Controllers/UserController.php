<?php namespace App\Http\Controllers;

use App;
use App\Models\User;
use Tymon\JWTAuth\JWTAuth;
use App\Extensions\Lock\Manager;
use Illuminate\Http\Request;
use App\Repositories\UserRepository as Repository;
use App\Http\Validators\UserValidator as Validator;
use Spira\Responder\Contract\ApiResponderInterface as Responder;

class UserController extends ApiController
{
    /**
     * Permission Lock Manager.
     *
     * @var Manager
     */
    protected $lock;

    /**
     * JWT Auth.
     *
     * @var Tymon\JWTAuth\JWTAuth
     */
    protected $jwtAuth;

    /**
     * Assign dependencies.
     *
     * @param  Repository  $repository
     * @param  Validator   $validator
     * @param  Lock        $lock
     * @param  JWTAuth     $jwtAuth
     * @param  Request     $request
     * @param  Responder   $responder
     * @return void
     */
    public function __construct(Repository $repository, Validator $validator, Manager $lock, JWTAuth $jwtAuth, Request $request, Responder $responder)
    {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->lock = $lock;
        $this->jwtAuth = $jwtAuth;
        $this->responder = $responder;

        $this->permissions($request);
    }

    /**
     * Set permissions to be used in the controller.
     *
     * @param  Request  $request
     * @return void
     */
    public function permissions(Request $request)
    {
        $this->lock->setRole(User::$userTypes);
        $user = $this->jwtAuth->user();
        $owner = [User::class, 'userIsOwner', $user, last($request->segments())];

        $this->lock->role(User::USER_TYPE_ADMIN)->permit(['readAll', 'readOne', 'update', 'delete']);
        $this->lock->role(User::USER_TYPE_GUEST)->permit(['readOne', 'update'], [$owner]);

        $this->middleware('permission:readAll', ['only' => 'getAll']);
        $this->middleware('permission:readOne', ['only' => 'getOne']);
        $this->middleware('permission:update', ['only' => 'patchOne']);
        $this->middleware('permission:delete', ['only' => 'deleteOne']);
    }
}
