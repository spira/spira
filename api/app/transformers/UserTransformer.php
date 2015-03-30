<?php

class UserTransformer extends BaseTransformer
{

    public function transform(\Eloquent $object) {
        $transformedObject = parent::transform($object);

        return $transformedObject;
    }

}