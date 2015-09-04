<?php

namespace App\Extensions\Lock;

use BeatSwitch\Lock\Callers\CallerLock as CallerLockBase;

class CallerLock extends CallerLockBase
{
    use LockTrait;
}
