<?php namespace App\Services;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Collection;

class Timezones
{
    /**
     * Get all timezones.
     *
     * @return Collection
     */
    public function all()
    {
        $allTimezones = DateTimeZone::listIdentifiers();

        $timezones = new Collection();
        $now = time();

        foreach ($allTimezones as $timezoneIdentifier) {
            $dateTimeZone = new DateTimeZone($timezoneIdentifier);

            //Read the current transition to get if the timezone is currently in DST
            $transitions = $dateTimeZone->getTransitions($now, $now);

            $timezones->push(new Collection([
                'timezone_identifier' => $dateTimeZone->getName(),
                'offset' => $offset = $dateTimeZone->getOffset(new DateTime()),
                'is_dst' => $transitions[0]['isdst'], //only use the first transition
                'display_offset' => $this->formatDisplayOffset($offset),
            ]));
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

        return ($offset >= 0 ? '+':'-') . $hoursFormatted;
    }
}
