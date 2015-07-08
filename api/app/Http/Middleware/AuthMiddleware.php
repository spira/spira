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
        $token = $this->jwtAuth->getTokenFromRequest($request);
        $user = $this->jwtAuth->getUser($token);

        if ($user->user_type !== 'admin') {
            throw new ForbiddenException;
        }

        return $next($request);
    }
}
