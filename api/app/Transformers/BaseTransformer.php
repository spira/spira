<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Illuminate\Contracts\Support\Arrayable;

class BaseTransformer extends TransformerAbstract
{
    /**
     * Turn the object into a format adjusted array.
     *
     * @param  Illuminate\Contracts\Support\Arrayable $object
     * @return array
     */
    public function transform(Arrayable $object)
    {
        $array = $object->toArray();

        // Transform array keys to camelCase
        foreach ($array as $key => $value) {
            if (str_contains($key, '_')) {
                $array = $this->renameArrayKey($array, $key, camel_case($key));
            }
        }

        return $array;
    }

    /**
     * Rename an array key while preserving array order.
     *
     * @param  array   $array
     * @param  string  $from
     * @param  string  $to
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
}
