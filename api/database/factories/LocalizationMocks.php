<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

$factory->define(App\Models\Localization::class, function ($faker) {
    return [
        'localizable_id' => $faker->uuid,
        'localizable_type' => $faker->word,
        'region_code' => $faker->randomElement(['au', 'uk', 'us']),
        'localizations' => json_encode([
            'varchar' => $faker->word,
            'text' => $faker->paragraph(3),
        ]),
    ];
});