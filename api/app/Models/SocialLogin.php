<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spira\Core\Model\Model\BaseModel;

class SocialLogin extends BaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['user_id', 'provider'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['provider', 'token'];

    /**
     * Overrides UuidTrait boot method.
     *
     * We override it with an empty method as we simply just want to prevent the
     * event registration to automatically create a UUID primary key, as we do
     * not use that in this model.
     *
     * @return void
     */
    protected static function bootUuidTrait()
    {
    }

    /**
     * Set the keys for a save update query.
     *
     * @param   Builder  $query
     *
     * @return  Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        foreach ($this->primaryKey as $key) {
            $query->where($key, '=', $this->getKeyValueForSaveQuery($key));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    protected function getKeyValueForSaveQuery($key)
    {
        if (isset($this->original[$key])) {
            return $this->original[$key];
        }

        return $this->getAttribute($key);
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }
}
