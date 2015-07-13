<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 13.07.15
 * Time: 19:24
 */

namespace Spira\Repository\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spira\Repository\Collection\Collection;

class BaseModel extends Model
{


    protected $isDeleted = false;

    public function __isset($key)
    {
        return parent::__isset($key);
    }

    public function __unset($key)
    {
        parent::__unset($key);
    }


    public function __get($key)
    {
        return parent::__get($key);
    }

    public function __set($key, $value)
    {
        parent::__set($key, $value);
    }


    /**
     * Save the model and all of its relationships.
     *
     * @return bool
     */
    public function push()
    {
        if (!$this->save()) {
            return false;
        }

        // To sync all of the relationships to the database, we will simply spin through
        // the relationships and save each model via this "push" method, which allows
        // us to recurse into all of these nested relations for the model instance.
        foreach ($this->relations as $key => $models) {
            $models = $models instanceof Collection
                ? $models->all() : [$models];



            foreach (array_filter($models) as $model) {
                if (!$model->push()) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function preserveKeys($models, Relation $relation)
    {

    }

    /**
     * @param array $options
     * @return bool|null
     * @throws \Exception
     */
    public function save(array $options = [])
    {
        if ($this->isDeleted()){
            return $this->delete();
        }

        return parent::save($options);

    }

    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }


    public function markAsDeleted()
    {
        $this->isDeleted = true;
    }

}