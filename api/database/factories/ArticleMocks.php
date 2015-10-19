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

$factory->define(App\Models\Article::class, function (\Faker\Generator $faker) {

    /** @var Collection $users */
    static $users = null;

    if (is_null($users)) {
        $users = \App\Models\User::all();
    }

    return [
        'article_id' => $faker->uuid,
        'title' => $faker->sentence,
        'status' => $faker->randomElement(App\Models\Article::$statuses),
        'excerpt' => Str::words($faker->realText(100), 30, ''),
        'primary_image' => $faker->imageUrl(500, 500, 'food'),
        'permalink' => $faker->boolean(90) ? $faker->unique()->slug : null,
        'author_id' => $users->random(1)->user_id,
        'author_display' => $faker->boolean(50),
        'show_author_promo' => $faker->boolean(50),
        'first_published' => $faker->boolean(90) ? $faker->dateTimeThisDecade()->format('Y-m-d H:i:s') : null,
        'sections_display' => null,
    ];
});

$factory->define(App\Models\ArticlePermalink::class, function (\Faker\Generator $faker) {
    return [
        'permalink' => $faker->unique()->slug,
    ];
});

$factory->define(App\Models\ArticleMeta::class, function (\Faker\Generator $faker) {
    return [
        'meta_id' => $faker->uuid,
        'meta_name' => $faker->boolean(50) ? $faker->randomElement(['name','description','keyword','canonical']) : $faker->word,
        'meta_content' => $faker->slug,
    ];
});

$factory->define(App\Models\ArticleComment::class, function (\Faker\Generator $faker) {
    return [
        'article_comment_id' => $faker->unique()->randomNumber,
        'body' => $faker->paragraph,
        'created_at' => $faker->dateTime,
    ];
});

$factory->define(App\Models\ArticleImage::class, function (\Faker\Generator $faker) {
    return [
        'article_image_id' => $faker->uuid,
        'image_type' => $imageType = $faker->optional()->randomElement(['primary','thumbnail','carousel']),
        'position' => ($imageType == 'carousel') ? $faker->numberBetween(1, 10) : null,
        'alt' => $faker->optional()->sentence,
        'title' => $faker->optional()->sentence,
    ];
});
