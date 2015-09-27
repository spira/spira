<?php

namespace Spira\Rbac\Item;

use Illuminate\Contracts\Auth\Authenticatable;

abstract class Rule
{
    /**
     * Executes the rule.
     *
     * @param Authenticatable $user the user ID. This should be either an integer or a string representing
     * the unique identifier of a user.
     * @param array $params parameters passed to check.
     * @return boolean a value indicating whether the rule permits the auth item it is associated with.
     */
    abstract public function execute(Authenticatable $user, $params);
}
