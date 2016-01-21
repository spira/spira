<?php

namespace App\Http\Controllers;

use App\Models\Role;

return [

    //basic route based permissions
    UtilityController::class . '@getSystemInformation' => [
        'type' => 'permission',
        'description' => 'Retrieve system information about the latest build',
    ],


    //roles
    Role::ADMIN_ROLE => [
        'children' => [
            UtilityController::class . '@getSystemInformation',
        ],
    ],
];