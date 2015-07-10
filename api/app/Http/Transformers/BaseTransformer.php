<?php

namespace app\Http\Transformers;

use Illuminate\Contracts\Support\Arrayable;
use League\Fractal\TransformerAbstract;

class BaseTransformer extends TransformerAbstract
{
    /**
     * Turn the object into a format adjusted array.
     *
     * @param Illuminate\Contracts\Support\Arrayable $object
     *
     * @return array
     */
    public function transform(Arrayable $object)
    {
        $array = $object->toArray();

        foreach ($array as $key => $value) {

            // Handle snakecase conversion in sub arrays
            if (is_array($value)) {
                $value = $this->renameKeys($value);
                $array[$key] = $value;
            }

            // Find any potential snake_case keys in the 'root' array, and
            // convert them to camelCase
            if (is_string($key) && str_contains($key, '_')) {
                $array = $this->renameArrayKey($array, $key, camel_case($key));
            }
        }

        // Rename self to _self
        $array = $this->renameSelfKey($array);

        return $array;
    }

    /**
     * Rename an array key while preserving array order.
     *
     * @param array  $array
     * @param string $from
     * @param string $to
     *
     * @return array
     */
    protected function renameArrayKey(array $array, $from, $to)
    {
        $keys = array_keys($array);
        $index = array_search($from, $keys);

        if ($index !== false) {
            $keys[$index] = $to;
            $array = array_combine($keys, $array);
        }

        return $array;
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

            // Convert snake_case to camelCase
            if (is_string($key) && str_contains($key, '_')) {
                $newArray[camel_case($key)] = $value;
            } else {
                $newArray[$key] = $value;
            }
        }

        // Update potential self keys
        $newArray = $this->renameSelfKey($newArray);

        return $newArray;
    }

    /**
     * Renames the key self to _self if it exists.
     *
     * @param array $array
     *
     * @return $array
     */
    protected function renameSelfKey(array $array)
    {
        if (array_key_exists('self', $array)) {
            $array = ['_self' => array_pull($array, 'self')] + $array;
        }

        return $array;
    }
}
