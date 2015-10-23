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
     * Model validation.
     *
     * @var array
     */
    protected static $validationRules = [
        'region_code' => 'required|supported_region',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = ['localizations', 'region_code'];

    /**
     * Save the model.
     *
     * @param array $options
     * @return mixed
     */
    public function save(array $options = [])
    {
        Cache::forever(self::getCacheKey($this->localizable_id, $this->region_code), $this->localizations);

        parent::save($options);
    }

    /**
     * Get the cache key for this model.
     *
     * @param $entityId
     * @param $regionCode
     * @return string
     */
    private static function getCacheKey($entityId, $regionCode)
    {
        return sprintf(self::cacheKeyBuilder, $entityId, $regionCode);
    }

    /**
     * Get localization from cache.
     *
     * @param $entityId
     * @param $regionCode
     * @return mixed
     */
    public static function getFromCache($entityId, $regionCode)
    {
        return json_decode(Cache::get(self::getCacheKey($entityId, $regionCode)), true);
    }
}
