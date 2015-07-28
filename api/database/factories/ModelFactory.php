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
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'phone' => $faker->optional(0.5)->phoneNumber,
        'mobile' => $faker->optional(0.5)->phoneNumber,
        'country' => $faker->randomElement(['AU', 'BE', 'DE', 'NZ', 'US']),
        'timezone_identifier' => $faker->timezone,
        'user_type' => $faker->randomElement(App\Models\User::$userTypes),
    ];
});

$factory->defineAs(App\Models\User::class, 'admin', function ($faker) use ($factory) {
    $user = $factory->raw(App\Models\User::class);

    return array_merge($user, ['userType' => App\Models\User::USER_TYPE_ADMIN]);
});

$factory->define(App\Models\UserCredential::class, function ($faker) {
    return [
        'user_credential_id' => $faker->uuid,
        'password' => 'password'
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

$factory->define(App\Models\AuthToken::class, function ($faker) {

    $user = factory(App\Models\User::class)->make();

    $jwtAuth = \App::make('Tymon\JWTAuth\JWTAuth');
    $token = $jwtAuth->fromUser($user);

    return ['token' => $token];
});
