<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Extensions\Lock\Manager;
use App\Extensions\JWTAuth\JWTAuth;
use App\Exceptions\ForbiddenException;

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
     * @return void
     */
    public function __construct(JWTAuth $jwtAuth, Manager $lock)
    {
        $this->jwtAuth = $jwtAuth;
        $this->lock = $lock;
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
    public function handle(Request $request, Closure $next, $action, $resource = null)
    {
        $user = $this->jwtAuth->getUser();
        $lock = $this->lock->makeCallerLockAware($user);

        if (!$user->can($action, $resource)) {
            throw new ForbiddenException;
        }

        return $next($request);
    }
}
