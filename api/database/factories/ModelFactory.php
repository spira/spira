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

$factory->define(App\Models\AuthToken::class, function ($faker) {

    $hostname = env('APP_HOSTNAME', 'localhost');

    $user = factory(App\Models\User::class)->make();
    $now = new Carbon();

    $body = [
        'iss' => $hostname,
        'aud' => str_replace('.api', '', $hostname),
        'sub' => $user->user_id,
        'nbf' => $now->timestamp,
        'iat' => $now->timestamp,
        'exp' => $now->addHour(1)->timestamp,
        'jti' => $faker->regexify('[A-Za-z0-9]{8}'),
        'user' => $user->toArray()
    ];

    $jwtAuth = \App::make('Tymon\JWTAuth\JWTAuth');
    $token = $jwtAuth->fromUser($user);

    return compact('token') + $body;
});

//$factory->defineAs(App\Models\AuthToken::class, 'expired', function($faker){
//    $tokenModel = factory(App\Models\User::class)->make([]);
//})