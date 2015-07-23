<?php namespace App\Extensions\Lock;

use BeatSwitch\Lock\Roles\Role;
use BeatSwitch\Lock\Callers\Caller;
use BeatSwitch\Lock\LockFactory as LockFactoryBase;

class LockFactory
{
    /**
     * Creates a new Lock instance from a caller and a driver.
     *
     * @param  Caller   $caller
     * @param  Manager  $manager
     * @return \BeatSwitch\Lock\Lock
     */
    public static function makeCallerLock(Caller $caller, Manager $manager)
    {
        return new CallerLock($caller, $manager);
    }

    /**
     * Creates a new Lock instance from a caller and a driver.
     *
     * @param  Role     $role
     * @param  Manager  $manager
     * @return \BeatSwitch\Lock\Lock
     */
    public static function makeRoleLock(Role $role, Manager $manager)
    {
        return new RoleLock($role, $manager);
    }
}
