<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Model\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

trait LocalizableTrait
{
    /**
     * Save the localised model attributes to the database if provided.
     *
     * @param  array  $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        if (array_key_exists('locale', $options)) {
            return $this->saveLocalisedAttributes($options);
        }

        return parent::save($options);
    }

    /**
     * Saves localised attributes for model.
     *
     * @param  array $options
     *
     * @return bool
     */
    protected function saveLocalisedAttributes(array $options)
    {
        $locale = array_pull($options, 'locale');

        if (! $localised = $this->getLocalisedModel($locale)) {
            $this->updateLocalisedCache($locale, $options);

            return DB::table('localisations')->insert([
                'entity_id' => $this->getKey(),
                'region_code' => $locale,
                'localisations' => json_encode($options),
            ]);
        } else {
            $localised = array_merge($localised, $options);
            $this->updateLocalisedCache($locale, $localised);

            return DB::table('localisations')
                ->where('entity_id', $this->getKey())
                ->where('region_code', $locale)
                ->update(['localisations' => json_encode($localised)]);
        }
    }

    /**
     * Get localised attributes for model.
     *
     * @param  string $locale
     *
     * @return mixed
     */
    protected function getLocalisedModel($locale)
    {
        $localised = DB::table('localisations')
            ->where('entity_id', $this->getKey())
            ->where('region_code', $locale)
            ->first();

        if ($localised) {
            return json_decode($localised->localisations, true);
        }

        return false;
    }

    /**
     * Updates cached version of localised model.
     *
     * @param  string $locale
     * @param  array  $localised
     *
     * @return void
     */
    protected function updateLocalisedCache($locale, array $localised = [])
    {
        $key = sprintf('l10n:%s:%s', $this->getKey(), $locale);

        Cache::put($key, json_encode($localised), 0);
    }
}
