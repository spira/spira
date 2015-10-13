<?php


namespace App\Http\Transformers;


use Spira\Rbac\Item\Assignment;

class PermissionsTransformer extends BaseTransformer
{

    /**
     * @param $object
     * @return mixed
     */
    public function transform($object)
    {
        if ($object instanceof Assignment)
        {
            return $object->roleName;
        }

        return null;
    }
}