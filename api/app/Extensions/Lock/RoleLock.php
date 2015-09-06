<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Lock;

use BeatSwitch\Lock\Roles\RoleLock as RoleLockBase;

class RoleLock extends RoleLockBase
{
    use LockTrait;
}
