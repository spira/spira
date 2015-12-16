<?php

namespace App\Http\Controllers;

use App\Models\Role;

return [

    //basic route based permissions
    UserController::class.'@getOne' => [
        'type' => 'permission',
        'description' => 'Get single user record by id',
    ],
    UserController::class.'@getAllPaginated' => [
        'type' => 'permission',
        'description' => 'Get all users',
    ],
    UserController::class.'@patchOne' => [
        'type' => 'permission',
        'description' => 'Update user record by id',
    ],
    UserController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Delete user by id',
    ],
    UserProfileController::class.'@getOne' => [
        'type' => 'permission',
        'description' => 'Get user profile record by id',
    ],
    UserProfileController::class.'@putOne' => [
        'type' => 'permission',
        'description' => 'Update/Add user profile record by id',
    ],
    UserProfileController::class.'@patchOne' => [
        'type' => 'permission',
        'description' => 'Update user profile record by id',
    ],


    //special permissions (hierarchy or rules)
    'ManipulateWithOwn' => [
        'children' => [
            UserController::class.'@getOne',
            UserController::class.'@patchOne',
            UserProfileController::class.'@getOne',
            UserProfileController::class.'@patchOne',
            UserProfileController::class.'@putOne',
        ],
    ],

    //roles
    Role::ADMIN_ROLE => [
        'children' => [
            UserController::class.'@getOne',
            UserController::class.'@getAllPaginated',
            UserController::class.'@patchOne',
            UserController::class.'@deleteOne',
            UserProfileController::class.'@getOne',
        ],
    ]
];