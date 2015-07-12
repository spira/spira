<?php

namespace App\Http\Permissions;

use BeatSwitch\Lock\Lock;
use BeatSwitch\Lock\Permissions\Condition;
use BeatSwitch\Lock\Permissions\Permission;
use BeatSwitch\Lock\Resources\Resource;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;

class SelfCondition implements Condition
{
    /**
     * JWT Auth.
     *
     * @var JWTAuth
     */
    protected $jwtAuth;

    /**
     * Illuminate Request.
     *
     * @var Request
     */
    protected $request;

    /**
     * Assign dependencies.
     *
     * @param  JWTAuth  $auth
     * @param  Request  $request
     * @return void
     */
    public function __construct(JWTAuth $jwtAuth, Request $request)
    {
        $this->jwtAuth = $jwtAuth;
        $this->request = $request;
    }

    /**
     * Assert if the condition is correct.
     *
     * @param  Lock           $lock
     * @param  Permission     $permission
     * @param  string         $action
     * @param  Resource|null  $resource
     * @return bool
     */
    public function assert(Lock $lock, Permission $permission, $action, Resource $resource = null)
    {
        $user = $this->jwtAuth->getUser($this->request);

        return $user->user_id == last($this->request->segments());
    }
}
