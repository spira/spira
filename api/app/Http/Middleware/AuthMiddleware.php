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
        // @todo Consolidate this retrieve/verify token code with the controller refresh() method.
        if (!$token = $this->jwtAuth->setRequest($request)->getToken()) {
            throw new BadRequestException('Token not provided.');
        }

        try {
            $user = $this->jwtAuth->authenticate((string) $token);
        }
        catch (TokenExpiredException $e) {
            throw new UnauthorizedException('Token expired.', 401, $e);
        }
        catch (TokenInvalidException $e) {
            throw new UnprocessableEntityException($e->getMessage(), 422, $e);
        }

        if (!$user) {
            throw new RuntimeException('The user does not exist.');
        }

        if ($user->user_type !== 'admin') {
            throw new ForbiddenException;
        }

        return $next($request);
    }
}
