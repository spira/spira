<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

$factory->define(App\Models\Image::class, function (\Faker\Generator $faker) {
    return [
            'image_id' => $faker->uuid,
            'version' => $faker->dateTimeThisDecade()->getTimestamp(),
            'folder' => $faker->lexify('????????'),
            // http://cloudinary.com/documentation/image_transformations#format_conversion
            'format' => $faker->randomElement(['jpg', 'png', 'gif', 'bmp', 'tiff', 'ico', 'pdf', 'eps', 'psd', 'svg', 'WebP']),
            'alt' => $faker->sentence,
            'title' => $faker->optional()->sentence,
    ];
});
