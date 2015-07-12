<?php namespace App\Http\Middleware;

use Closure;
use App\Models\User;
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
    protected $lock;

    /**
     * Assign dependencies.
     *
     * @param  JWTAuth  $jwtAuth
     * @param  Manager  $lock
     * @return void
     */
    public function __construct(JWTAuth $jwtAuth, Manager $lock)
    {
        $this->jwtAuth = $jwtAuth;
        $this->lock = $lock;

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
        $lock = $this->lock->makeCallerLockAware($user);

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
        $this->lock->setRole(User::$userTypes, 'guest');

        foreach (User::$permissions as $role => $resources) {
            foreach ($resources as $resource => $actions) {
                foreach ($actions as $action) {
                    if (is_array($action)) {
                        $condition = \App::make('App\Http\Permissions\\'.$action[1]);
                        $this->lock->role($role)->allow($action[0], $resource, null, $condition);
                    } else {
                        $this->lock->role($role)->allow($action, $resource);
                    }
                }
            }
        }
    }
}
