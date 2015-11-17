<?php


namespace App\Http\Transformers;


class UserTransformer extends EloquentModelTransformer
{
    public $nestedMap = [
        'roles' => RoleTransformer::class,
        'permissions' => PermissionsTransformer::class,
    ];
}