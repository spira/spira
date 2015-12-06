<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Support\Str;
use Spira\Model\Collection\Collection;

$postAttributes = function (\Faker\Generator $faker) {

    /** @var Collection $users */
    static $users = null;

    if (is_null($users)) {
        $users = \App\Models\User::all();
    }

    $authorOverride = $faker->boolean();

    return [
        'post_id' => $faker->uuid,
        'title' => $faker->sentence,
        'status' => $faker->randomElement(App\Models\AbstractPost::$statuses),
        'excerpt' => Str::words($faker->realText(100), 30, ''),
        'thumbnail_image_id' => null,
        'permalink' => $faker->boolean(90) ? $faker->unique()->slug : null,
        'author_id' => $users->random(1)->user_id,
        'author_override' => $authorOverride ? $faker->name : null,
        'author_website' => $authorOverride ? $faker->optional()->url : null,
        'show_author_promo' => $faker->boolean(50),
        'first_published' => $faker->boolean(90) ? $faker->dateTimeThisDecade()->format('Y-m-d H:i:s') : null,
        'sections_display' => null,
        'users_can_comment' => false,
        'public_access' => false
    ];
};

$factory->define(App\Models\Article::class, $postAttributes);

$factory->define(App\Models\PostPermalink::class, function (\Faker\Generator $faker) {
    return [
        'permalink' => $faker->unique()->slug,
    ];
});

$factory->define(App\Models\PostComment::class, function (\Faker\Generator $faker) {
    return [
        'post_comment_id' => $faker->unique()->randomNumber,
        'body' => $faker->paragraph,
        'created_at' => $faker->dateTime,
    ];
});
