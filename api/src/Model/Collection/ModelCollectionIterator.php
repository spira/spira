<?php

/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 13.07.15
 * Time: 19:59.
 */

namespace Spira\Model\Collection;

use Spira\Model\Model\BaseModel;

class ModelCollectionIterator extends \FilterIterator
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @param \Iterator|array $items
     */
    public function __construct($items)
    {
        if (is_array($items)) {
            $this->items = $items;
        }
    }

    public function rewind()
    {
        reset($this->items);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->items);
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return key($this->items);
    }

    /**
     * @return mixed
     */
    public function next()
    {
        return next($this->items);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $key = key($this->items);
        $var = ($key !== null && $key !== false);

        return $var;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Check whether the current element of the iterator is acceptable.
     * @link http://php.net/manual/en/filteriterator.accept.php
     * @return bool true if the current element is acceptable, otherwise false.
     */
    public function accept()
    {
        $item = $this->getInnerIterator()->current();
        if ($item instanceof BaseModel) {
            if ($item->isDeleted()) {
                return false;
            }
        }

        return true;
    }
}
