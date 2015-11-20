<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

$factory->define(App\Models\Meta::class, function (\Faker\Generator $faker) {
    return [
        'meta_id' => $faker->uuid,
        'meta_name' => $faker->boolean(50) ? $faker->randomElement(['name','description','keyword','canonical']) : $faker->word,
        'meta_content' => $faker->slug,
    ];
});
