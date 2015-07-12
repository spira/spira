<?php namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Timezones
 * @package App\Models
 *
 * Note this model does not have an associated database table as it is an abstract
 * data model with generated token data.
 */
class Timezones extends Model
{
    /**
     * Get the collection of available timezones.
     *
     * @return Collection
     */
    public static function getTimezones()
    {
        $allTimezones = \DateTimeZone::listIdentifiers();

        $timezones = new Collection();
        $now = time();

        foreach ($allTimezones as $timezoneIdentifier) {
            $dateTimeZone = new \DateTimeZone($timezoneIdentifier);

            //Read the current transition to get if the timezone is currently in DST
            $transitions = $dateTimeZone->getTransitions($now, $now);

            $timezones->push(new Collection([
                'timezone_identifier' => $dateTimeZone->getName(),
                'offset' => $offset = $dateTimeZone->getOffset(new \DateTime()),
                'is_dst' => $transitions[0]['isdst'], //only use the first transition
                'display_offset' => self::getDisplayOffset($offset),
            ]));
        }

        return $timezones;
    }

    private static function getDisplayOffset($offset)
    {
        $inital = new \DateTime();
        $inital->setTimestamp(abs($offset));
        $hoursFormatted = $inital->format('H:i');


        return ($offset >= 0 ? '+':'-') . $hoursFormatted;
    }
}
