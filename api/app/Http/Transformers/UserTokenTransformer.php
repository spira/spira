<?php


namespace App\Http\Transformers;


use App\Models\User;

class UserTokenTransformer extends EloquentModelTransformer
{
    public function transform($object)
    {
        /** @var User $object */
        $object->setHidden(['roles']);
        $roles = $object->roles->lists('key')->toArray();
        $object = parent::transform($object);
        $object['roles'] = $roles;

        return $object;
    }
}