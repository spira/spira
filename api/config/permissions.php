<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Auth\ImpersonateNonAdmin;
use App\Http\Auth\ManipulateWithOwn;
use App\Models\Role;

return [

    //basic permissions
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
    PermissionsController::class.'@getUserRoles' => [
        'type' => 'permission',
        'description' => 'Get all roles assigned to user',
    ],

    'impersonateUser' =>  [
        'type' => 'permission',
        'description' => 'Permission to allow a user to log in as another user',
    ],
    'impersonateAllUsers' =>  [
        'type' => 'permission',
        'description' => 'Permission to allow a user to log in as any other user',
        'children' => [
            'impersonateUser',
        ],
    ],
    'impersonateNonAdmin' =>  [
        'type' => 'permission',
        'description' => 'Permission to allow a user to log in as non-admin users',
        'ruleName' => ImpersonateNonAdmin::class,
        'children' => [
            'impersonateUser',
        ],
    ],

    //special permissions
    'manipulateWithOwn' => [
        'type' => 'permission',
        'description' => 'General permission to update record which belongs to the user',
        'ruleName' => ManipulateWithOwn::class,
        'children' => [
            UserController::class.'@getOne',
            UserController::class.'@patchOne',
            PermissionsController::class.'@getUserRoles',
        ],
    ],

    //roles
    Role::SUPER_ADMIN_ROLE => [
        'type' => 'role',
        'description' => 'Super Admin role, can do all actions',
        'children' => [
            Role::ADMIN_ROLE,
            'impersonateAllUsers',
        ],
    ],
    Role::ADMIN_ROLE => [
        'type' => 'role',
        'description' => 'Admin role',
        'children' => [
            UserController::class.'@getOne',
            UserController::class.'@getAllPaginated',
            UserController::class.'@patchOne',
            UserController::class.'@deleteOne',
            PermissionsController::class.'@getUserRoles',
            'impersonateNonAdmin',
            Role::USER_ROLE,
        ],
    ],
    Role::USER_ROLE => [
        'type' => 'role',
        'children' => [
            'manipulateWithOwn',
        ],
    ],
    'testrole' => [
        'type' => 'role',
        'description' => 'Simple test role',
    ],
];
