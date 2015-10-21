<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Spira\Model\Model\BaseModel;
use Spira\Model\Model\CompoundKeyTrait;

class Localization extends BaseModel
{
    use CompoundKeyTrait;

    const cacheKeyBuilder = 'l10n:%s:%s';

    /**
     * The primary key for the model.
     *
     * @var array
     */
    protected $primaryKey = ['localizable_id', 'localizable_type', 'region_code'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = ['localizations', 'region_code'];

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

    /**
     * Save the model.
     *
     * @param array $options
     * @return mixed
     */
    public function save(array $options = [])
    {
        Cache::put($this->getCacheKey(), $this->localizations, 0);

        parent::save($options);
    }

    /**
     * Get the cache key for this model.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return sprintf(self::cacheKeyBuilder, $this->entity_id, $this->region_code);
    }
}
