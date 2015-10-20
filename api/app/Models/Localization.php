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
use Illuminate\Support\Facades\DB;
use Spira\Model\Model\BaseModel;
use Spira\Model\Model\CompoundKeyTrait;

class Localization extends BaseModel
{
    use CompoundKeyTrait;

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

    /**
     * Save the model. This function has been overridden because Laravel is unable to save using composite keys.
     *
     * @return mixed
     */
    public function save()
    {
        $this->updateCache();

        if ($this->exists) {
            return DB::table('localizations')
                ->where('entity_id', $this->entity_id)
                ->where('region_code', $this->region_code)
                ->update(['localizations' => json_encode($this->localizations)]);
        }
        else {
            return DB::table('localizations')->insert([
                'entity_id' => $this->entity_id,
                'region_code' => $this->region_code,
                'localizations' => json_encode($this->localizations),
            ]);
        }
    }

    private function updateCache()
    {
        $key = sprintf('l10n:%s:%s', $this->entity_id, $this->region_code);

        Cache::put($key, json_encode($this->localizations), 0);
    }
}
