<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 21:36
 */

namespace App\Http\Transformers;

use App\Helpers\RouteHelper;
use Illuminate\Contracts\Support\Arrayable;
use Spira\Repository\Model\BaseModel;
use Traversable;

class IlluminateModelTransformer extends BaseTransformer
{
    public static $badRoutes = [];
    /**
     * Turn the object into a format adjusted array.
     *
     * @param  $object
     * @return array
     */
    public function transform($object)
    {
        $array = null;
        if ($object instanceof Arrayable) {
            $array = $object->toArray();
        }

        if (is_null($array) && is_array($object)) {
            $array = $object;
        }

        if (is_null($array)) {
            throw new \InvalidArgumentException('must be array or '.Arrayable::class);
        }

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

        if (($object instanceof BaseModel)) {
            $this->addSelfKey($object, $array);
        }

        return $array;
    }

    /**
     * Recursive adding of self key
     * @param BaseModel $model
     * @param $array
     */
    protected function addSelfKey(BaseModel $model, &$array)
    {
        if ($route = RouteHelper::getRoute($model)) {
            $array['_self'] = $route;
        }

        foreach ($model->getRelations() as $key => $value) {
            $camelCaseKey = camel_case($key);
            if ($value instanceof BaseModel && isset($array[$camelCaseKey])) {
                $this->addSelfKey($value, $array[$camelCaseKey]);
            } elseif ($this->isIterable($value)) {
                foreach ($value as $index => $relatedModel) {
                    if ($relatedModel instanceof BaseModel && isset($array[$camelCaseKey][$index])) {
                        $this->addSelfKey($relatedModel, $array[$camelCaseKey][$index]);
                    }
                }
            }
        }
    }

    /**
     * @param $var
     * @return bool
     */
    protected function isIterable($var)
    {
        return (is_array($var) || $var instanceof Traversable);
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

        return $newArray;
    }
}