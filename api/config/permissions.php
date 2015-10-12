<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

return  [
  //basic permissions
  UserController::class.'@getOne' =>  [
      'type' => 'permission',
      'description' => 'Get single user record by id',
    ],
  UserController::class.'@getAllPaginated' =>  [
      'type' => 'permission',
      'description' => 'Get all users',
    ],
  UserController::class.'@patchOne' =>  [
      'type' => 'permission',
      'description' => 'Update user record by id',
    ],
  UserController::class.'@deleteOne' =>  [
      'type' => 'permission',
      'description' => 'Delete user by id',
    ],
  //special permissions
  'manipulateWithOwn' =>  [
          'type' => 'permission',
          'description' => 'General permission to update record which belongs to the user',
          'ruleName' => 'App\\Http\\Auth\\ManipulateWithOwn',
          'children' =>  [
                  UserController::class.'@getOne',
                  UserController::class.'@patchOne',
              ],
      ],

  //roles
  'admin' =>  [
      'type' => 'role',
      'description' => 'Admin role',
      'children' =>  [
          UserController::class.'@getOne',
          UserController::class.'@getAllPaginated',
          UserController::class.'@patchOne',
          UserController::class.'@deleteOne',
          'user',
      ],
    ],
  'user' =>  [
      'type' => 'role',
      'children' =>  [
        'manipulateWithOwn',
      ],
    ],
  'testrole' => [
      'type' => 'role',
      'description' => 'Simple test role',
  ]
];
