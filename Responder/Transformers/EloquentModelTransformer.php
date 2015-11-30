<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\Responder\Transformers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Spira\Core\Helpers\RouteHelper;
use Spira\Core\Model\Collection\Collection;
use Spira\Core\Model\Model\BaseModel;
use Spira\Core\Responder\Contract\TransformerInterface;

class EloquentModelTransformer extends BaseTransformer
{
    public static $badRoutes = [];

    public $addSelfKey = true;

    public $nestedMap = [];

    /**
     * Turn the object into a format adjusted array.
     *
     * @param  $object
     * @return array
     */
    public function transform($object)
    {
        if (is_null($object)) {
            return;
        }

        if (($object instanceof BaseModel)) {
            if ($this->isCreated()) {
                $this->applyCreated($object);
            }
            $this->applyLocalizations($object);
        }

        $array = null;
        if ($object instanceof Arrayable) {
            $array = $object->toArray();
        }

        if (is_null($array) && is_array($object)) {
            $array = $object;
        }

        if (is_null($array)) {
            throw new \InvalidArgumentException('must be array or '.Arrayable::class.' instead got '.gettype($object));
        }

        if (($object instanceof BaseModel)) {
            $castTypes = $object['casts'];
            foreach ($array as $key => $value) {
                $array[$key] = $this->castAttribute($castTypes, $key, $value);
            }
        }

        foreach ($array as $key => $value) {

            // Handle snakecase conversion in sub arrays
            if (is_array($value) || is_object($value)) {
                $value = $this->renameKeys((array) $value);
                $array[$key] = $value;
            }

            // Find any potential snake_case keys in the 'root' array, and
            // convert them to camelCase
            if (is_string($key) && str_contains($key, '_')) {
                $array = $this->renameArrayKey($array, $key, $this->camelCase($key));
            }
        }

        if (($object instanceof BaseModel)) {
            if ($this->addSelfKey) {
                $array = $this->addSelfKey($object, $array);
            }

            $array = $this->nestRelations($object, $array);
        }

        return $array;
    }

    /**
     * Cast an attribute from a PHP type.
     *
     * @param $castTypes
     * @param $key
     * @param $value
     * @return mixed
     * @internal param BaseModel $model
     * @internal param $object
     */
    private function castAttribute($castTypes, $key, $value)
    {
        if (! array_key_exists($key, $castTypes)) {
            return $value;
        }

        $castType = $castTypes[$key];

        if ($value instanceof Carbon) {
            switch ($castType) {
                case 'date':
                    return $value->format('Y-m-d');
                default:
                    return $value->toIso8601String();
            }
        }

        return $value;
    }

    /**
     * Recursive adding of self key.
     * @param BaseModel $model
     * @param $array
     * @return array
     */
    protected function addSelfKey(BaseModel $model, $array)
    {
        if ($route = RouteHelper::getRoute($model)) {
            $array = ['_self' => $route] + $array;
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
            $value = (is_array($value) || is_object($value)) ? $this->renameKeys((array) $value) : $value;

            // Convert snake_case to camelCase
            if (is_string($key) && str_contains($key, '_')) {
                $newArray[$this->camelCase($key)] = $value;
            } else {
                $newArray[$key] = $value;
            }
        }

        return $newArray;
    }

    /**
     * Convert a string to camelCase with preserved starting underscore.
     *
     * @param  string  $str
     * @return string
     */
    protected function camelCase($str)
    {
        // camel_case() will strip away starting _, so put it back
        $prefix = starts_with($str, '_') ? '_' : '';

        return $prefix.camel_case($str);
    }

    /**
     * Get the objects nested entities transformed.
     * @param $object
     * @param $array
     * @return mixed
     */
    private function nestRelations(BaseModel $object, $array)
    {
        /** @var BaseModel $object */
        if (count($object['relations']) > 0) {
            foreach ($object['relations'] as $relation => $childModelOrCollection) {
                if (in_array($relation, $object->getHidden())) {
                    continue;
                }
                /** @var TransformerInterface $transformer */
                $transformer = $this->getTransformerForNested($relation);
                $childTransformed = null;
                if ($childModelOrCollection instanceof Collection) {
                    $childTransformed = $transformer->transformCollection($childModelOrCollection, $this->options);
                } else {
                    $childTransformed = $transformer->transformItem($childModelOrCollection, $this->options);
                }

                $array = $array + ['_'.$relation => $childTransformed];
                unset($array[$relation]);
            }
        }

        return $array;
    }

    /**
     * @param string $relationName
     * @param string $default
     * @return TransformerInterface
     */
    private function getTransformerForNested($relationName, $default = self::class)
    {
        if (isset($this->nestedMap[$relationName])) {
            $className = $this->nestedMap[$relationName];
        } else {
            $className = $default;
        }

        return new $className($this->getService());
    }

    /**
     * Attempt to find localizations in cache and replace the attributes with the items.
     * @param $object
     */
    protected function applyLocalizations(BaseModel $object)
    {
        if (! isset($this->options['region']) || ! $object instanceof LocalizableModelInterface) {
            return;
        }

        if ($localizations = Localization::getFromCache($object->getKey(), $this->options['region'])) {
            foreach ($localizations as $attribute => $localization) {
                if (is_array($localization) && is_array($object->$attribute)) {
                    $object->$attribute = $this->mergeRecursive($object->$attribute, $localization);
                } else {
                    $object->$attribute = $localization;
                }
            }
        }
    }

    /**
     * Attempt to find localizations in cache and replace the attributes with the items.
     * @param $object
     */
    protected function applyCreated(BaseModel $object)
    {
        $object->setVisible(['']);
    }

    /**
     * @return bool
     */
    protected function isCreated()
    {
        return isset($this->options['created']) && $this->options['created'];
    }

    /**
     * Recursively replace primary array items with replacements, ignores nulls in replacements,  so array_replace_recursive cannot be used.
     * @param array $primaryArray
     * @param array $replacements
     * @return array
     */
    private function mergeRecursive(array $primaryArray, array $replacements)
    {
        foreach ($primaryArray as $key => &$value) {
            if (isset($replacements[$key])) {
                if (is_array($value)) {
                    $value = $this->mergeRecursive($value, $replacements[$key]);
                } else {
                    $value = $replacements[$key];
                }
            }
        }

        return $primaryArray;
    }
}
