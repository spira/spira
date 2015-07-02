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

use App\Services\ModelFactory;
use Carbon\Carbon;

$factory->define(App\Models\User::class, function ($faker) {
    return [
        'user_id' => $faker->uuid,
        'email' => $faker->email,
        'password' => Hash::make('password'),
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'phone' => $faker->optional(0.5)->phoneNumber,
        'mobile' => $faker->optional(0.5)->phoneNumber,
    ];
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


/**
 * The body for the json web token
 */
$factory->defineAs(App\Models\AuthToken::class, 'body', function (Faker\Generator $faker) use ($factory) {
    $hostname = env('APP_HOSTNAME', 'localhost');

    $user = $factory->make(App\Models\User::class)->toArray();
    unset($user['password']);

    $now = new Carbon();
    return [
        'iss' => $hostname,
        'aud' => str_replace('.api', '', $hostname),
        'sub' => $user['user_id'],
        'nbf' => $now->toDateTimeString(),
        'iat' => $now->toDateTimeString(),
        'exp' => $now->addHour(1)->toDateTimeString(),
        'jti' => $faker->regexify('[A-Za-z0-9]{8}'),
        '#user' => $user,
    ];
});

/**
 * This is the json web token response. It relies on the App\Models\AuthToken::class 'body' factory above
 */
$factory->defineAs(App\Models\AuthToken::class, 'token', function (Faker\Generator $faker) use ($factory) {

    $factoryTransformer = new ModelFactory;

    $body = $factoryTransformer->json([App\Models\AuthToken::class, 'body']);

    $header = [
        'alg' => "RS256",
        'typ' => "JWT",
    ];

    $signature = $faker->regexify('[A-Za-z0-9]{30}'); //note the signature is not a true encoding of the auth certificate.

    $token = base64_encode(json_encode($header)) .".". base64_encode($body) .".". $signature;

    return [
        'token' => $token,
        'decoded_token_body' => json_decode($body),
    ];

});