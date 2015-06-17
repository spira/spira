<?php namespace App\Http\Middleware;

use Closure;

class TransformInputData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        foreach ($request->all() as $key => $value) {

            // Find any potential camelCase keys, and convert them to snake_case
            if (!ctype_lower($key)) {
                $request->offsetSet(snake_case($key), $value);
                $request->offsetUnset($key);
            }

        }

        return $next($request);
    }
}
