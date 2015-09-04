<?php

namespace App\Extensions\Lock;

use BeatSwitch\Lock\Roles\RoleLock as RoleLockBase;

class RoleLock extends RoleLockBase
{
    use LockTrait;
}
