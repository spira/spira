<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Extensions\JWTAuth\JWTAuth;

class UserResolverMiddleware
{
    /**
     * JWT Auth.
     *
     * @var JWTAuth
     */
    protected $jwtAuth;

    /**
     * Assign dependencies.
     *
     * @param  JWTAuth  $jwtAuth
     *
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
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $request->setUserResolver(function () {
            return $this->jwtAuth->user();
        });

        return $next($request);
    }
}
