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
use App\Models\Role;
use App\Http\Auth\ReAssignNonAdmin;

return [

    //basic route based permissions
    PermissionsController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all roles assigned to user',
    ],
    PermissionsController::class.'@putMany' => [
        'type' => 'permission',
        'description' => 'Reassign user roles',
    ],
    AuthController::class.'@loginAsUser' =>  [
        'type' => 'permission',
        'description' => 'Permission to allow a user to log in as another user',
    ],

    //special permissions (hierarchy or rules)
    'ReAssignAllRoles' =>  [
        'type' => 'permission',
        'description' => 'Permission to allow a user to assign and detach any role',
        'children' => [
            PermissionsController::class.'@putMany',
        ],
    ],
    'ReAssignNonAdmin' =>  [
        'type' => 'permission',
        'description' => 'Permission to allow a user to assign and detach non-admin roles only',
        'ruleName' => ReAssignNonAdmin::class,
        'children' => [
            PermissionsController::class.'@putMany',
        ],
    ],
    'ImpersonateAllUsers' =>  [
        'type' => 'permission',
        'description' => 'Permission to allow a user to log in as any other user',
        'children' => [
            AuthController::class.'@loginAsUser',
        ],
    ],
    'ImpersonateNonAdmin' =>  [
        'type' => 'permission',
        'description' => 'Permission to allow a user to log in as non-admin users',
        'ruleName' => ImpersonateNonAdmin::class,
        'children' => [
            AuthController::class.'@loginAsUser',
        ],
    ],
    'ManipulateWithOwn' => [
        'children' => [
            PermissionsController::class.'@getAll',
        ],
    ],

    //roles
    Role::SUPER_ADMIN_ROLE => [
        'children' => [
            'ImpersonateAllUsers',
            'ReAssignAllRoles',
        ],
    ],
    Role::ADMIN_ROLE => [
        'children' => [
            PermissionsController::class.'@getAll',
            'ImpersonateNonAdmin',
            'ReAssignNonAdmin',
        ],
    ],

];
