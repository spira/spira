<?php

namespace App\Http\Auth;


use Illuminate\Contracts\Auth\Authenticatable;
use Spira\Rbac\Item\Rule;

class ManipulateWithOwn extends Rule
{

    /**
     * Executes the rule.
     *
     * @param callable $userResolver
     * @param array $params parameters passed to check.
     * @return boolean a value indicating whether the rule permits the auth item it is associated with.
     */
    public function execute(callable $userResolver, $params)
    {
        /** @var Authenticatable $user */
        $user = $userResolver();
        return isset($params['model']) ? $params['model']->user_id == $user->getAuthIdentifier() : false;
    }
}