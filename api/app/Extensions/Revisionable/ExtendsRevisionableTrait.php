<?php

namespace App\Extensions\Revisionable;

use App;
use Venturecraft\Revisionable\RevisionableTrait;

trait ExtendsRevisionableTrait
{
    use RevisionableTrait;

    /**
     * Attempt to find the user id of the currently logged in user.
     *
     * @return string|null
     */
    private function getUserId()
    {
        $jwtAuth = App::make('Tymon\JWTAuth\JWTAuth');

        if ($user = $jwtAuth->user()) {
            return $user->user_id;
        }

        return null;
    }
}
