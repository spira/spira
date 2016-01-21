<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Models\Role;

return [

    //basic route based permissions
    UtilityController::class.'@getSystemInformation' => [
        'type' => 'permission',
        'description' => 'Retrieve system information about the latest build',
    ],

    //roles
    Role::ADMIN_ROLE => [
        'children' => [
            UtilityController::class.'@getSystemInformation',
        ],
    ],
];
