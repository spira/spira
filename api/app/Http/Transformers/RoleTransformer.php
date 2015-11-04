<?php

namespace App\Http\Transformers;

use Spira\Rbac\Item\Item;

class RoleTransformer extends EloquentModelTransformer
{
    public $addSelfKey = false;

    public $nestedMap = [
        'permissions' => PermissionsTransformer::class
    ];

    /**
     * @param $object
     * @return mixed
     */
    public function transform($object)
    {
        $object = parent::transform($object);
        $object['type'] = Item::TYPE_ROLE;
        return $object;
    }
}