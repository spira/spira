<?php
use App\Http\Auth\ManipulateWithOwn;
use App\Http\Auth\ManipulateWithOwnChild;
use App\Models\Role;

return [

    //special permissions (hierarchy or rules)

    'ManipulateWithOwn' => [
        'type' => 'permission',
        'description' => 'General permission to update record which belongs to the user',
        'ruleName' => ManipulateWithOwn::class,
    ],
    'ManipulateWithOwnChild' => [
        'type' => 'permission',
        'description' => 'General permission to update record which belongs to the user',
        'ruleName' => ManipulateWithOwnChild::class,
    ],

    //roles

    Role::SUPER_ADMIN_ROLE => [
        'type' => 'role',
        'description' => 'Super Admin role, can do all actions',
        'children' => [
            Role::ADMIN_ROLE,
        ],
    ],
    Role::ADMIN_ROLE => [
        'type' => 'role',
        'description' => 'Admin role',
        'children' => [
            Role::USER_ROLE,
        ],
    ],
    Role::USER_ROLE => [
        'type' => 'role',
        'children' => [
            'ManipulateWithOwn',
            'ManipulateWithOwnChild',
        ],
    ],
    'testrole' => [
        'type' => 'role',
        'description' => 'Simple test role',
    ],
];