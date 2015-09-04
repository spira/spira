<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TransformInputDataMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->transformRequestInput($request, $request->all());
        $this->transformRequestInput($request, $request->json());

        return $next($request);
    }

    /**
     * Recursively rename keys in nested arrays.
     *
     * @param array $array
     *
     * @return array
     */
    protected function renameKeys(array $array)
    {
        $newArray = [];
        foreach ($array as $key => $value) {

            // Recursively check if the value is an array that needs parsing too
            $value = (is_array($value)) ? $this->renameKeys($value) : $value;

            // Convert camelCase to snake_case
            if (is_string($key) && ! ctype_lower($key)) {
                $newArray[snake_case($key)] = $value;
            } else {
                $newArray[$key] = $value;
            }
        }

        return $newArray;
    }

    /**
     * @param $request
     */
    protected function transformRequestInput(Request $request, $input)
    {
        foreach ($input as $key => $value) {

            // Handle snakecase conversion in sub arrays
            if (is_array($value)) {
                $value = $this->renameKeys($value);
                $request->offsetSet($key, $value);
            }

            // Find any potential camelCase keys in the 'root' array, and convert
            // them to snake_case
            if (! ctype_lower($key)) {
                // Only convert if the key will change
                if ($key != snake_case($key)) {
                    $request->offsetSet(snake_case($key), $value);
                    $request->offsetUnset($key);
                }
            }
        }
    }
}
