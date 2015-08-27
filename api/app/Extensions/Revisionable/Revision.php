<?php

namespace App\Extensions\Revisionable;

use App\Models\User;
use Venturecraft\Revisionable\Revision as RevisionBase;

class Revision extends RevisionBase
{
    /**
     * User Responsible.
     *
     * @return User
     */
    public function userResponsible()
    {
        return User::find($this->user_id);
    }
}
