<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Model\Collection;

use Spira\Model\Model\BaseModel;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    /**
     * @var null
     */
    protected $className;

    /**
     * Create a new collection.
     *
     * @param  mixed $items
     * @param string|null $className
     */
    public function __construct($items = [], $className = null)
    {
        $items = is_array($items) ? $items : $this->getArrayableItems($items);
        foreach ($items as $item) {
            $this->add($item);
        }
        $this->className = $className;
    }

    public function count()
    {
        return count(array_filter($this->items, function (BaseModel $item) {
            return ! $item->isDeleted();
        }));
    }

    /**
     * @param bool $includingMarkedForDeletion
     * @return array
     */
    public function all($includingMarkedForDeletion = false)
    {
        if ($includingMarkedForDeletion) {
            return $this->items;
        }

        return array_filter($this->items, function (BaseModel $item) {
            return ! $item->isDeleted();
        });
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
        if ($item instanceof BaseModel) {
            $this->items[$this->getItemKey($item)] = $item;
        } else {
            $this->items[] = $item;
        }

        return $this;
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
     * @param BaseModel $model
     * @return string
     */
    protected function getItemHash(BaseModel $model)
    {
        return spl_object_hash($model);
    }

    /**
     * @param BaseModel $model
     * @return mixed|string
     */
    protected function getItemKey(BaseModel $model)
    {
        if ($model->exists) {
            return $model->getKey();
        }

        return $this->getItemHash($model);
    }

    /**
     * @return string Name of the Model collection consist of
     */
    public function getClassName()
    {
        return $this->className;
    }
}
