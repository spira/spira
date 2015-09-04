<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Lock;

use BeatSwitch\Lock\Lock;
use BeatSwitch\Lock\Permissions\Privilege as BasePrivilege;

class Privilege extends BasePrivilege
{
    /**
     * Check all the conditions and make sure they all return true.
     *
     * @param  Lock    $lock
     * @param  string  $action
     * @param  \BeatSwitch\Lock\Resources\Resource|null $resource
     * @return bool
     */
    protected function resolveConditions(Lock $lock, $action, $resource)
    {
        foreach ($this->conditions as $condition) {
            $class = array_shift($condition);
            $method = array_shift($condition);

            if (! call_user_func_array([$class, $method], $condition)) {
                return false;
            }
        }

        return true;
    }
}
