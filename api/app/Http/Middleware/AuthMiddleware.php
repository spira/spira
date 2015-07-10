<?php namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Exceptions\ForbiddenException;

class AuthMiddleware
{
    /**
     * JWT Auth
     *
     * @var JWTAuth
     */
    protected $jwtAuth;

    /**
     * Assign dependencies.
     *
     * @param  JWTAuth  $jwtAuth
     * @return void
     */
    public function __construct(JWTAuth $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get possible x amount of parameters passed to the middleware
        $userTypes = array_slice(func_get_args(), 2);

        $user = $this->jwtAuth->getUser($request);

        // Check the restriction types
        if (in_array('admin', $userTypes) and $user->user_type == 'admin') {
            $allowed = true;
        }

        if (in_array('self', $userTypes) and in_array($user->user_id, $request->segments())) {
            $allowed = true;
        }

        if (!isset($allowed)) {
            throw new ForbiddenException;
        }

        return $next($request);
    }
}
