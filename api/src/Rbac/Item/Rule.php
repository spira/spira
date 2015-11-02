<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Item;

use Spira\Rbac\User\UserProxy;

abstract class Rule
{
    /**
     * Executes the rule.
     *
     * @param UserProxy $userProxy
     * @param array $params parameters passed to check.
     * @return bool a value indicating whether the rule permits the auth item it is associated with.
     */
    abstract public function execute(UserProxy $userProxy, $params);
}
