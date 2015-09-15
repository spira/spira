<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

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
}
