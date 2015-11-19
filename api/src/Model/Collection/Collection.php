<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Model\Collection;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    /**
     * @var null
     */
    public $className;

    /**
     * Create a new collection.
     *
     * @param  mixed $items
     * @param string|null $className
     */
    public function __construct($items = [], $className = null)
    {
        parent::__construct($items);
        $this->className = $className;
    }

    /**
     * Key an associative array by a field or using a callback.
     *
     * @param  callable|string  $keyBy
     * @return static
     */
    public function keyBy($keyBy)
    {
        $keyBy = $this->valueRetriever($keyBy);

        $results = [];

        foreach ($this->items as $item) {
            $results[$keyBy($item)] = $item;
        }

        $this->items = $results;

        return $this;
    }

    /**
     * Add an item to the collection.
     *
     * @param  mixed  $item
     * @return $this
     */
    public function add($item)
    {
        $this->checkItem($item);

        return parent::add($item);
    }

    /**
     * @param $item
     * @throws ItemTypeException
     */
    protected function checkItem($item)
    {
        $className = $this->className;
        if (! is_null($className) && ! ($item instanceof $className)) {
            throw new ItemTypeException('Item must be instance of '.$className);
        }
    }

    /**
     * @return string Name of the Model collection consist of
     */
    public function getClassName()
    {
        return $this->className;
    }
}
