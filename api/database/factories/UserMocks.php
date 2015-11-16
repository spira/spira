<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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
        'region_code' => $faker->optional(0.9)->randomElement(array_pluck(config('regions.supported'), 'code')),
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

$factory->define(App\Models\AuthToken::class, function () use ($factory) {

    /** @var \Spira\Auth\Driver\Guard $jwtAuth */
    $jwtAuth = Illuminate\Support\Facades\App::make('auth');

    $user = $factory->make(\App\Models\User::class);

    $token = $jwtAuth->generateToken($user);

    return ['token' => $token];
});

$factory->define(App\Models\Role::class, function (\Faker\Generator $faker) {
    return [
        'key' => $faker->word,
        'description' => $faker->sentence(),
        'is_default' => $faker->boolean(),
    ];
});

$factory->define(App\Models\Rating::class, function (\Faker\Generator $faker) {
    return [
        'rating_id' => $faker->uuid(),
        'rateable_id' => $faker->uuid(),
        'rateable_type' => $faker->randomElement(App\Models\Rating::$rateables),
        'rating_value' => $faker->numberBetween(1, 5),
    ];
});

$factory->define(App\Models\Bookmark::class, function (\Faker\Generator $faker) {
    return [
        'bookmark_id' => $faker->uuid(),
        'bookmarkable_id' => $faker->uuid(),
        'bookmarkable_type' => $faker->randomElement(App\Models\Bookmark::$bookmarkables),
    ];
});
