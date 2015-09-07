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

$factory->define(App\Models\TestEntity::class, function (\Faker\Generator $faker) {
    return [
        'entity_id' => $faker->uuid,
        'varchar' => $faker->word,
        'hash' => Hash::make($faker->word),
        'integer' => $faker->numberBetween(0, 500),
        'decimal' => $faker->randomFloat(2, 0, 100),
        'boolean' => $faker->boolean(),
        'nullable' => null,
        'text' => $faker->paragraph(3),
        'date' => $faker->date(),
        'multi_word_column_title' => true,
        'hidden' => $faker->boolean(),
    ];
});

$factory->defineAs(App\Models\TestEntity::class, 'custom', function ($faker) use ($factory) {
    $testEntity = $factory->raw(App\Models\TestEntity::class);

    return array_merge($testEntity, ['varchar' => 'custom']);
});

$factory->define(App\Models\User::class, function (\Faker\Generator $faker) {
    return [
        'user_id' => $faker->uuid,
        'username' => $faker->unique()->userName,
        'email' => $faker->unique()->email,
        'email_confirmed' => $faker->optional(0.9)->dateTimeThisYear(),
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'country' => $faker->randomElement(['AU', 'BE', 'DE', 'NZ', 'US']),
        'timezone_identifier' => $faker->timezone,
        'avatar_img_url' => $faker->optional(0.8)->imageUrl(500, 500, 'people'),
        'user_type' => $faker->randomElement(App\Models\User::$userTypes),
    ];
});

$factory->define(App\Models\UserProfile::class, function (\Faker\Generator $faker) {
    return [
        'phone' => $faker->optional(0.5)->phoneNumber,
        'mobile' => $faker->optional(0.5)->phoneNumber,
        'dob' => $faker->dateTimeThisCentury()->format('Y-m-d'),
        'gender' => $faker->optional(0.5)->randomElement(['M', 'F', 'N/A']),
        'about' => $faker->optional(0.5)->text(120),
        'facebook' => $faker->boolean() ? substr($faker->url(), 0, 100) : null,
        'twitter' => $faker->boolean() ? '@'.$faker->userName() : null,
        'pinterest' => $faker->boolean() ? substr($faker->url(), 0, 100) : null,
        'instagram' => $faker->boolean() ? substr($faker->url(), 0, 100) : null,
        'website' => $faker->boolean() ? substr($faker->url(), 0, 100) : null,
    ];
});

$factory->defineAs(App\Models\User::class, 'admin', function ($faker) use ($factory) {
    $user = $factory->raw(App\Models\User::class);

    return array_merge($user, ['userType' => App\Models\User::USER_TYPE_ADMIN]);
});

$factory->define(App\Models\UserCredential::class, function ($faker) {
    return [
        'password' => 'password',
    ];
});

$factory->define(App\Models\SocialLogin::class, function ($faker) {
    return [
        'provider' => $faker->randomElement(['facebook', 'google', 'twitter']),
        'token' => $faker->sha256,
    ];
});

$factory->define(App\Models\SecondTestEntity::class, function ($faker) {
    return [
        'entity_id' => $faker->uuid,
        'check_entity_id' => $faker->uuid,
        'value' => $faker->word,
    ];
});

$factory->defineAs(App\Models\TestEntity::class, 'custom', function ($faker) use ($factory) {
    $testEntity = $factory->raw(App\Models\TestEntity::class);

    return array_merge($testEntity, ['varchar' => 'custom']);
});

$factory->define(App\Models\AuthToken::class, function () use ($factory) {

    $jwtAuth = Illuminate\Support\Facades\App::make('Tymon\JWTAuth\JWTAuth');

    $user = $factory->make(\App\Models\User::class);

    $token = $jwtAuth->fromUser($user);

    return ['token' => $token];
});

$factory->define(App\Models\ArticlePermalink::class, function (\Faker\Generator $faker) {
    return [
        'permalink' => $faker->unique()->slug,
    ];
});

$factory->define(App\Models\ArticleMeta::class, function (\Faker\Generator $faker) {
    return [
        'meta_name' => $faker->unique()->slug,
        'meta_content' => $faker->slug,
        'meta_property' => $faker->slug,
    ];
});

$factory->define(App\Models\ArticleComment::class, function (\Faker\Generator $faker) {
    return [
        'article_comment_id' => $faker->unique()->randomNumber,
        'body' => $faker->paragraph,
        'created_at' => $faker->dateTime,
    ];
});

$factory->define(App\Models\Tag::class, function (\Faker\Generator $faker) {
    return [
        'tag_id' => $faker->uuid,
        'tag' => $faker->unique()->lexify('????????'),
    ];
});

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

$factory->define(App\Models\ArticleImage::class, function (\Faker\Generator $faker) {
    return [
        'article_image_id' => $faker->uuid,
        'image_type' => $imageType = $faker->optional()->randomElement(['primary','thumbnail','carousel']),
        'position' => ($imageType == 'carousel') ? $faker->numberBetween(1, 10) : null,
        'alt' => $faker->optional()->sentence,
        'title' => $faker->optional()->sentence,
    ];
});

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
        'content' => $content = $faker->realText(500),
        'excerpt' => Str::words($content, 30, ''),
        'primary_image' => $faker->imageUrl(500, 500, 'food'),
        'permalink' => $faker->boolean(90) ? $faker->unique()->slug : null,
        'author_id' => $users->random(1)->user_id,
        'first_published' => $faker->boolean(90) ? $faker->dateTimeThisDecade()->format('Y-m-d H:i:s') : null,
    ];
});

$factory->define(Venturecraft\Revisionable\Revision::class, function (\Faker\Generator $faker) {
    return [
        'revision_id' => $faker->uuid,
        'revisionable_type' => $faker->word,
        'revisionable_id' => $faker->uuid,
        'user_id' => $faker->uuid,
        'key' => $faker->word,
        'old_value' => $faker->word,
        'new_value' => $faker->word,
        'created_at' => $faker->dateTime,
    ];
});

$factory->defineAs(Venturecraft\Revisionable\Revision::class, 'article', function ($faker) use ($factory) {
    $revision = $factory->raw(Venturecraft\Revisionable\Revision::class);

    // Get 2 articles to simulate an update
    $articles = [];
    $articles[0] = array_except($factory->raw(App\Models\Article::class), ['article_id']);
    $articles[1] = array_except($factory->raw(App\Models\Article::class), ['article_id']);

    // Inject child entities
    foreach ($articles as &$article) {
        $tags = factory(App\Models\Tag::class, $faker->numberBetween(2, 4))->make();
        $article['tags'] = $tags->lists('tag')->toArray();

        $meta = $factory->raw(App\Models\ArticleMeta::class);
        $article['metas'] = $meta;
    }

    // Pick a key to update
    $key = array_keys($articles[0])[array_rand(array_keys($articles[0]))];
    $articleRevision = [
        'revisionable_type' => 'App\Models\Article',
        'key' => $key,
        'old_value' => $articles[0][$key],
        'new_value' => $articles[1][$key],
    ];

    return array_merge($revision, $articleRevision);
});
