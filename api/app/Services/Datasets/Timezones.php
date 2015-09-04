<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services\Datasets;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Collection;

class Timezones extends Dataset
{
    /**
     * Get all timezones.
     *
     * @return Collection
     */
    protected function getDataset()
    {
        $allTimezones = DateTimeZone::listIdentifiers();

        $timezones = new Collection();
        $now = time();

        foreach ($allTimezones as $timezoneIdentifier) {
            $dateTimeZone = new DateTimeZone($timezoneIdentifier);

            //Read the current transition to get if the timezone is currently in DST
            $transitions = $dateTimeZone->getTransitions($now, $now);

            $timezones->push([
                'timezone_identifier' => $dateTimeZone->getName(),
                'offset' => $offset = $dateTimeZone->getOffset(new DateTime()),
                'is_dst' => $transitions[0]['isdst'], //only use the first transition
                'display_offset' => $this->formatDisplayOffset($offset),
            ]);
        }

        return $timezones;
    }

    /**
     * Format the time offset for display.
     *
     * @param  int  $offset
     * @return string
     */
    protected function formatDisplayOffset($offset)
    {
        $inital = new DateTime();
        $inital->setTimestamp(abs($offset));
        $hoursFormatted = $inital->format('H:i');

        return ($offset >= 0 ? '+' : '-').$hoursFormatted;
    }
}
