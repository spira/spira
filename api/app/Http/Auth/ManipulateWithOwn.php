<?php

namespace App\Http\Auth;


use Illuminate\Contracts\Auth\Authenticatable;
use Spira\Rbac\Item\Rule;

class ManipulateWithOwn extends Rule
{

    /**
     * Executes the rule.
     *
     * @param Authenticatable $user the user ID. This should be either an integer or a string representing
     * the unique identifier of a user.
     * @param array $params parameters passed to check.
     * @return boolean a value indicating whether the rule permits the auth item it is associated with.
     */
    public function execute(Authenticatable $user, $params)
    {
        return isset($params['model']) ? $params['model']->user_id == $user->getAuthIdentifier() : false;
    }
}