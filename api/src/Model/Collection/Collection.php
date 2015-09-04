<?php

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

    public function count($includingMarkedForDeletion = false)
    {
        if ($includingMarkedForDeletion) {
            return count($this->items);
        }

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
            $this->preventAddingSameItem($item);
            $this->items[$this->getItemKey($item)] = $item;
        } else {
            $this->items[] = $item;
        }

        return $this;
    }

    /**
     * @param BaseModel $item
     */
    public function remove(BaseModel $item)
    {
        $this->checkItem($item);
        $key = $this->getItemKey($item);
        $model = null;
        if (isset($this->items[$key])) {
            $model = $this->items[$key];
        }

        if (is_null($model)) {
            $key = $this->getItemHash($item);
            if (isset($this->items[$key])) {
                $model = $this->items[$key];
            }
        }

        if (! is_null($model)) {
            /* @var BaseModel $model */
            $model->markAsDeleted();
        }
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
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ModelCollectionIterator($this->items);
    }

    /**
     * In case new entity was addedm then saved, then added again.
     * @param BaseModel $item
     */
    protected function preventAddingSameItem(BaseModel $item)
    {
        $hash = $this->getItemHash($item);
        if (isset($this->items[$hash])) {
            unset($this->items[$hash]);
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
}
