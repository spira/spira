<?php

namespace App\Extensions\Lock;

interface UserOwnership
{
    /**
     * Check if the user is owns the entity.
     *
     * @param  \App\Models\User  $user
     * @param  string            $entityId
     * @return bool
     */
    public static function userIsOwner($user, $entityId);
}
