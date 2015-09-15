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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spira\Model\Model\BaseModel;

class Localization extends BaseModel
{
    /**
     * The primary key for the model.
     *
     * @var array
     */
    protected $primaryKey = ['region_code', 'entity_id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Determine if a localized attribute exists on the model.
     *
     * @param  string  $attribute
     *
     * @return bool
     */
    public function hasLocalizedAttribute($attribute)
    {
        return array_key_exists($attribute, $this->getLocalizedAttributes());
    }

    /**
     * Get all localized attributes in model.
     *
     * @return array
     */
    public function getLocalizedAttributes()
    {
        return json_decode($this->localizations, true);
    }

    // @todo Refactor these methods into a trait, CompoundKeyTrait, that can be
    // used by sociallogin as well.

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
     * Find model by compound key.
     *
     * @param  string $region
     * @param  string $id
     *
     * @return BaseModel
     */
    public function findByCompoundKey($region, $id)
    {
        return $this->findOrFail($region, $id);
    }

    /**
     * Find a model by its compound key or throw an exception.
     *
     * @param  string $region
     * @param  mixed  $id
     * @param  array  $columns
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($region, $id, $columns = ['*'])
    {
        $result = $this->find($region, $id, $columns);

        if (! is_null($result)) {
            return $result;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($region, $id, $columns = ['*'])
    {
        $query = $this->newQuery()
            ->where('localizations.region_code', '=', $region)
            ->where('localizations.entity_id', '=', $id);

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
}
