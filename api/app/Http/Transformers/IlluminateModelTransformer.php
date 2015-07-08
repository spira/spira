<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 21:36
 */

namespace App\Http\Transformers;

use Illuminate\Database\Eloquent\Model;

class IlluminateModelTransformer extends BaseTransformer implements ItemTransformerInterface, CollectionTransformerInterface
{
    /**
     * Turn the object into a format adjusted array.
     *
     * @param  $object
     * @return array
     */
    public function transform($object)
    {
        if (!($object instanceof Model)){
            throw new \InvalidArgumentException('Must be '.Model::class);
        }

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

        $array['_self'] = route(get_class($object),['id'=>$object->getQueueableId()]);

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

    /**
     * Recursively rename keys in nested arrays.
     *
     * @param  array  $array
     * @return array
     */
    protected function renameKeys(array $array)
    {
        $newArray = [];
        foreach($array as $key => $value) {

            // Recursively check if the value is an array that needs parsing too
            $value = (is_array($value)) ? $this->renameKeys($value) : $value;

            // Convert snake_case to camelCase
            if (is_string($key) && str_contains($key, '_')) {
                $newArray[camel_case($key)] = $value;
            } else {
                $newArray[$key] = $value;
            }
        }

        return $newArray;
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function transformCollection($collection)
    {
        return $this->getService()->collection($collection, $this);
    }

    /**
     * @param $item
     * @return mixed
     */
    public function transformItem($item)
    {
        return $this->getService()->item($item, $this);
    }
}