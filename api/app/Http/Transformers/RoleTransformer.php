<?php

namespace App\Http\Transformers;

class RoleTransformer extends EloquentModelTransformer
{
    public $addSelfKey = false;

    public $nestedMap = [
        'permissions' => PermissionsTransformer::class
    ];
}