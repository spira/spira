<?php


namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Spira\Contract\Exception\UnauthorizedException;

class AttachUserToEntityMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            throw new UnauthorizedException();
        }

        $request->merge(['user_id' => $user->user_id]);

        return $next($request);
    }
}