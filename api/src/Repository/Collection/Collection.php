<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 13.07.15
 * Time: 19:41
 */

namespace Spira\Repository\Collection;

use Spira\Repository\Model\BaseModel;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    /**
     * Add an item to the collection.
     *
     * @param  mixed  $item
     * @return $this
     */
    public function add($item)
    {
        if ($item instanceof BaseModel){

            $this->items[$this->getItemKey($item)] = $item;
        }else{
            $this->items[] = $item;
        }

        return $this;
    }

    /**
     * @param BaseModel $item
     */
    public function remove(BaseModel $item)
    {
        $key = $this->getItemKey($item);
        if (isset($this->items[$key])){
            /** @var BaseModel $model */
            $model = $this->items[$key];
            $model->markAsDeleted();
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
     * @param BaseModel $model
     * @return mixed|string
     */
    protected function getItemKey(BaseModel $model)
    {
        if ($model->exists){
            return $model->getKey();
        }

        return spl_object_hash($model);
    }

}