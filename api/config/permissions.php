<?php

namespace App\Http\Controllers;

return array (
  //basic permissions
  UserController::class.'@getOne' =>
    array (
      'type' => 'permission',
      'description' => 'Get single user record by id',
    ),
  UserController::class.'@getAllPaginated' =>
    array (
      'type' => 'permission',
      'description' => 'Get all users',
    ),
  UserController::class.'@patchOne' =>
    array (
      'type' => 'permission',
      'description' => 'Update user record by id',
    ),
  UserController::class.'@deleteOne' =>
    array (
      'type' => 'permission',
      'description' => 'Delete user by id',
    ),
  //special permissions
  'manipulateWithOwn' =>
      array (
          'type' => 'permission',
          'description' => 'General permission to update record which belongs to the user',
          'ruleName' => 'App\\Http\\Auth\\ManipulateWithOwn',
          'children' =>
              array (
                  UserController::class.'@getOne',
                  UserController::class.'@patchOne',
              ),
      ),

  //roles
  'admin' => 
    array (
      'type' => 'role',
      'description' => 'Admin role',
      'children' =>
      array (
          UserController::class.'@getOne',
          UserController::class.'@getAllPaginated',
          UserController::class.'@patchOne',
          UserController::class.'@deleteOne',
          'user'
      ),
    ),
  'user' => 
    array (
      'type' => 'role',
      'children' =>
      array (
        'manipulateWithOwn',
      ),
    )
);
