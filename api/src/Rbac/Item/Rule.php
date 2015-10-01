<?php

namespace Spira\Rbac\Item;

abstract class Rule
{
    /**
     * Executes the rule.
     *
     * @param callable $userResolver
     * @param array $params parameters passed to check.
     * @return boolean a value indicating whether the rule permits the auth item it is associated with.
     */
    abstract public function execute(callable $userResolver, $params);
}
