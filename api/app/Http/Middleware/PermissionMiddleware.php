<?php namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Exceptions\ForbiddenException;
use BeatSwitch\Lock\Manager;

class PermissionMiddleware
{
    /**
     * JWT Auth.
     *
     * @var JWTAuth
     */
    protected $jwtAuth;

    /**
     * Permission Lock Manager.
     *
     * @var Manager
     */
    protected $lockManager;

    /**
     * Assign dependencies.
     *
     * @param  JWTAuth  $jwtAuth
     * @param  Manager  $lockmanager
     * @return void
     */
    public function __construct(JWTAuth $jwtAuth, Manager $lockManager)
    {
        $this->jwtAuth = $jwtAuth;
        $this->lockManager = $lockManager;

        $this->assignPermissions();
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string   $action
     * @param  string   $resource
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $action, $resource)
    {
        $user = $this->jwtAuth->getUser($request);
        $lock = $this->lockManager->makeCallerLockAware($user);

        if (!$user->can($action, $resource)) {
            throw new ForbiddenException;
        }

        return $next($request);
    }

    /**
     * Assign permission to user types.
     *
     * @return void
     */
    protected function assignPermissions()
    {
        $this->lockManager->setRole(['public', 'admin'], 'guest');

        $this->lockManager->role('admin')->allow('readAll', 'users');
        $this->lockManager->role('admin')->allow('readOne', 'users');
        $this->lockManager->role('admin')->allow('update', 'users');
        $this->lockManager->role('admin')->allow('delete', 'users');

        $selfCondition = \App::make('App\Http\Permissions\SelfCondition');
        $this->lockManager->role('public')->allow('readOne', 'users', null, $selfCondition);
        $this->lockManager->role('public')->allow('update', 'users', null, $selfCondition);
    }
}
