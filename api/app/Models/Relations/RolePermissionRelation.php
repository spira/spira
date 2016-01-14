<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\Relations;

use App\Models\Role;
use Spira\Rbac\Item\Item;
use App\Models\Permission;
use \Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RolePermissionRelation extends HasMany
{
    use GateTrait;

    /**
     * @var string
     */
    private $roleKey;

    /**
     * RolePermissionRelation constructor.
     * @param string $roleKey
     */
    public function __construct($roleKey)
    {
        $this->roleKey = $roleKey;
        $this->related = new Role;
        $this->localKey = $this->getPlainForeignKey();
    }

    public function getResults()
    {
        return $this->get();
    }

    /**
     * @param Item[] $permissions
     * @return array
     */
    protected function hydratePermissions($permissions)
    {
        $permissionModels = [];
        foreach ($permissions as $permission) {
            $permissionModels[] = new Permission([
                'key' => $permission->name,
                'description' => $permission->description,
            ]);
        }

        return $permissionModels;
    }

    public function addEagerConstraints(array $models)
    {
    }

    public function get(){

        $storage = $this->getGate()->getStorage();

        $permissions = $this->getItemsRecursively(Item::TYPE_PERMISSION, $storage->getChildren($this->roleKey));
        return new Collection($this->hydratePermissions($permissions));
    }

    public function getPlainForeignKey()
    {
        return 'name';
    }

}
