<?php namespace App\Extensions\Lock;

use BeatSwitch\Lock\Callers\Caller;
use BeatSwitch\Lock\Manager as BaseManager;

class Manager extends BaseManager
{
    /**
     * Creates a new Lock instance for the given caller.
     *
     * @param  Caller $caller
     * @return CallerLock
     */
    public function caller(Caller $caller)
    {
        return LockFactory::makeCallerLock($caller, $this);
    }

    /**
     * Creates a new Lock instance for the given role.
     *
     * @param  \BeatSwitch\Lock\Roles\Role|string $role
     * @return RoleLock
     */
    public function role($role)
    {
        return LockFactory::makeRoleLock($this->convertRoleToObject($role), $this);
    }
}
