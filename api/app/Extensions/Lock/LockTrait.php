<?php namespace App\Extensions\Lock;

use BeatSwitch\Lock\Permissions\Restriction;

trait LockTrait
{
    /**
     * Give the subject permission to do something.
     *
     * @param  string|array  $action
     * @param  array         $conditions
     * @return void
     */
    public function permit($action, array $conditions = [])
    {
        $this->allow($action, null, null, $conditions);
    }

    /**
     * Give the subject permission to do something.
     *
     * @param  string|array  $action
     * @param  string|\BeatSwitch\Lock\Resources\Resource $resource
     * @param  int           $resourceId
     * @param  array         $conditions
     * @return void
     */
    public function allow($action, $resource = null, $resourceId = null, $conditions = [])
    {
        $actions = (array) $action;
        $resource = $this->convertResourceToObject($resource, $resourceId);
        $permissions = $this->getPermissions();

        foreach ($actions as $action) {
            foreach ($permissions as $key => $permission) {
                if ($permission instanceof Restriction && ! $permission->isAllowed($this, $action, $resource)) {
                    $this->removePermission($permission);
                    unset($permissions[$key]);
                }
            }

            // We'll need to clear any restrictions above
            $restriction = new Restriction($action, $resource);

            if ($this->hasPermission($restriction)) {
                $this->removePermission($restriction);
            }

            $this->storePermission(new Privilege($action, $resource, $conditions));
        }
    }
}
