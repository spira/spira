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
use App\Http\Auth\ManipulateWithOwnChild;
use App\Http\Auth\ReAssignNonAdmin;
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
    PermissionsController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all roles assigned to user',
    ],
    PermissionsController::class.'@putManyReplace' => [
        'type' => 'permission',
        'description' => 'Reassign user roles',
    ],
    ArticleRateController::class.'@putOne' => [
        'type' => 'permission',
        'description' => 'Rate article or change rating value',
    ],
    ArticleBookmarkController::class.'@putOne' => [
        'type' => 'permission',
        'description' => 'Add to bookmarks',
    ],
    ArticleRateController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Remove article rating',
    ],
    ArticleBookmarkController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Remove from bookmarks',
    ],
    'ReAssignAllRoles' =>  [
        'type' => 'permission',
        'description' => 'Permission to allow a user to assign and detach any role',
        'children' => [
            PermissionsController::class.'@putManyReplace',
        ],
    ],
    'ReAssignNonAdmin' =>  [
        'type' => 'permission',
        'description' => 'Permission to allow a user to assign and detach non-admin roles only',
        'ruleName' => ReAssignNonAdmin::class,
        'children' => [
            PermissionsController::class.'@putManyReplace',
        ],
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
            UserProfileController::class.'@getOne',
            UserProfileController::class.'@patchOne',
            UserProfileController::class.'@putOne',
            PermissionsController::class.'@getAll',
        ],
    ],
    'ManipulateWithOwnChild' => [
        'type' => 'permission',
        'description' => 'General permission to update record which belongs to the user',
        'ruleName' => ManipulateWithOwnChild::class,
        'children' => [
            ArticleRateController::class.'@putOne',
            ArticleBookmarkController::class.'@putOne',
            ArticleRateController::class.'@deleteOne',
            ArticleBookmarkController::class.'@deleteOne',
        ],
    ],

    //roles
    Role::SUPER_ADMIN_ROLE => [
        'type' => 'role',
        'description' => 'Super Admin role, can do all actions',
        'children' => [
            Role::ADMIN_ROLE,
            'impersonateAllUsers',
            'ReAssignAllRoles',
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
            UserProfileController::class.'@getOne',
            PermissionsController::class.'@getAll',
            'impersonateNonAdmin',
            'ReAssignNonAdmin',
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
