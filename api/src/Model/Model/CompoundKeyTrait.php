<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Model\Model;

use LogicException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait CompoundKeyTrait
{
    /**
     * Overrides UuidTrait boot method.
     *
     * We override it with an empty method as we simply just want to prevent the
     * event registration to automatically create a UUID primary key, as we do
     * not use that in models with compound key.
     *
     * @return void
     */
    protected static function bootUuidTrait()
    {
    }

    /**
     * Boot CompoundKeyTrait.
     *
     * @throws  \LogicException
     *
     * @return  void
     */
    protected static function bootCompoundKeyTrait()
    {
        $vars = get_class_vars(__CLASS__);
        if (! is_array($vars['primaryKey'])) {
            throw new LogicException('primaryKey must be an array');
        }
    }

    /**
     * Find model by compound key.
     *
     * @param  array $compound
     *
     * @return BaseModel
     */
    public function findByCompoundKey(array $compound)
    {
        return $this->findOrFail($compound);
    }

    /**
     * Find a model by its compound key or throw an exception.
     *
     * @param  array $compound
     * @param  array $columns
     *
     * @return BaseModel
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail($compound, $columns = ['*'])
    {
        $result = $this->find($compound, $columns);

        if (! is_null($result)) {
            return $result;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }

    /**
     * Find a model by its compound key.
     *
     * @param  array $compound
     * @param  array $columns
     *
     * @return BaseModel|null
     */
    public function find(array $compound, $columns = ['*'])
    {
        $query = $this->newQuery();

        foreach ($compound as $column => $value) {
            $query->where($this->getQualifiedColumnName($column), '=', $value);
        }

        return $query->first($columns);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string|array $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        // If the key is an array, it's the compound key, which there won't be
        // any value to return
        if (is_array($key)) {
            return;
        }

        return parent::getAttribute($key);
    }

    /**
     * Insert the given attributes on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $attributes
     *
     * @return void
     */
    protected function insertAndSetId(Builder $query, $attributes)
    {
        // With the case of a compount primary key, we do not have a specific id
        // to set, so we override this method and leave out the part to set id.
        $query->insert($attributes);
    }

    /**
     * Get the table qualified column name.
     *
     * @return string
     */
    public function getQualifiedColumnName($column)
    {
        return $this->getTable().'.'.$column;
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  $query
     * @return $query
     */
    protected function setKeysForSaveQuery($query)
    {
        foreach($this->getKeyName() as $key) {
            $query->where($key, '=', $this->getAttribute($key));

        }

        return $query;
    }
}
