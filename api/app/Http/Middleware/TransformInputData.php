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

            // Handle snakecase conversion in sub arrays
            if (is_array($value)) {
                $value = $this->renameKeys($value);
            }

            // Find any potential camelCase keys in the 'root' array, and convert
            // them to snake_case
            if (!ctype_lower($key)) {
                $request->offsetSet(snake_case($key), $value);
                $request->offsetUnset($key);
            }

        }

        return $next($request);
    }

    /**
     * Recursively rename keys in nested arrays.
     *
     * @param  array  $array
     * @return array
     */
    protected function renameKeys(array $array)
    {
        foreach($array as $key => $value) {

            // Recursively check if the value is an array that needs parsing too
            $value = (is_array($value)) ? $this->renameKeys($value) : $value;

            // Convert camelCase to snake_case
            if (!ctype_lower($key)) {
                $array[snake_case($key)] = $value;
                unset($array[$key]);
            }

        }

        return $array;
    }
}
