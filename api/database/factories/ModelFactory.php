<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use Illuminate\Support\Str;
use Carbon\Carbon;

// Ensure that the custom validation rules are registered so the factories also
// have them available.
Validator::resolver(function ($translator, $data, $rules, $messages) {
    return new \App\Services\SpiraValidator($translator, $data, $rules, $messages);
});

$factory->define(App\Models\TestEntity::class, function ($faker) {
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
        'hidden' => $faker->boolean()
    ];
});

$factory->defineAs(App\Models\TestEntity::class, 'custom', function ($faker) use ($factory) {
    $testEntity = $factory->raw(App\Models\TestEntity::class);

    return array_merge($testEntity, ['varchar' => 'custom']);
});


$factory->define(App\Models\User::class, function ($faker) {
    return [
        'user_id' => $faker->uuid,
        'email' => $faker->email,
        'email_confirmed' => $faker->optional(0.9)->dateTimeThisYear($max = 'now'),
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'country' => $faker->randomElement(['AU', 'BE', 'DE', 'NZ', 'US']),
        'timezone_identifier' => $faker->timezone,
        'user_type' => $faker->randomElement(App\Models\User::$userTypes),
    ];
});

$factory->define(App\Models\UserProfile::class, function ($faker) {
    return [
        'phone' => $faker->optional(0.5)->phoneNumber,
        'mobile' => $faker->optional(0.5)->phoneNumber,
        'avatar_img_url' => $faker->optional(0.8)->imageUrl(500, 500, 'people'),
        'dob' => $faker->dateTimeThisCentury()->format('Y-m-d')
    ];
});

$factory->defineAs(App\Models\User::class, 'admin', function ($faker) use ($factory) {
    $user = $factory->raw(App\Models\User::class);

    return array_merge($user, ['userType' => App\Models\User::USER_TYPE_ADMIN]);
});

$factory->define(App\Models\UserCredential::class, function ($faker) {
    return [
        'password' => 'password'
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
        'value' => $faker->word
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

$factory->define(App\Models\Article::class, function (\Faker\Generator $faker) {

    return [
        'article_id' => $faker->uuid,
        'title' => $faker->sentence,
        'status' => $faker->randomElement(App\Models\Article::$statuses),
        'content' => $content = implode("\n\n", $faker->paragraphs(3)),
        'excerpt' => Str::words($content, 30, ''),
        'primary_image' => $faker->imageUrl(500, 500, 'food'),
        'permalink' => $faker->boolean(90) ? $faker->unique()->slug : null,
        'first_published' => $faker->boolean(90) ? $faker->dateTimeThisDecade()->format('Y-m-d H:i:s') : null,
    ];
});
