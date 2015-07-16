<?php namespace App\Services;

use Illuminate\Support\Collection;
use Faker\Factory as Faker;

/**
 * @todo Consider consolidating Timezones and Countries into one service that
 *       can provide different kinds of static/generated data.
 */
class Countries
{
    public function all()
    {
        $faker = Faker::create();
        $countries = [];

        for ($i=0; $i < 10; $i++) {
            array_push($countries, new Collection([
                'country_code' => $faker->countryCode,
                'country_name' => $faker->country
            ]));
        }

        return new Collection($countries);
    }
}
