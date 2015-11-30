<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\Model\Model;

use LogicException;
use Illuminate\Database\Eloquent\Builder;

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
        if (! is_array(self::getPrimaryKey())) {
            throw new LogicException('primaryKey must be an array');
        }
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
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        foreach ($this->getKeyName() as $key) {
            $query->where($key, '=', $this->getAttribute($key));
        }

        return $query;
    }
}
