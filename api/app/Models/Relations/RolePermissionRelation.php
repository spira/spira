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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RolePermissionRelation extends HasMany
{
    use GateTrait;

    /**
     * @var array
     */
    private $roleKeys = [];

    /**
     * RolePermissionRelation constructor.
     * @param Role $parent
     */
    public function __construct(Role $parent)
    {
        $this->related = $parent;
        $this->parent = $parent;

        $this->localKey = 'key';
        $this->foreignKey = $parent->getKey();
    }

    public function getResults()
    {
        return $this->get();
    }

    /**
     * @param Item[] $permissions
     * @param $roleKey
     * @return array
     */
    protected function hydratePermissions($permissions, $roleKey)
    {
        $permissionModels = [];
        foreach ($permissions as $permission) {
            $permissionModels[] = new Permission([
                'key' => $permission->name,
                'description' => $permission->description,
                'parent_role_key' => $roleKey,
            ]);
        }

        return $permissionModels;
    }

    /**
     * @param array $models
     */
    public function addEagerConstraints(array $models)
    {
        $this->roleKeys = collect($models)->pluck('key');
    }

    /**
     * @return Collection
     */
    public function get()
    {
        $storage = $this->getGate()->getStorage();
        $allPermissions = new Collection;

        if (empty($this->roleKeys)) {
            $this->roleKeys = [$this->foreignKey];
        }

        collect($this->roleKeys)->each(function ($roleKey) use ($storage, $allPermissions) {
            $permissions = $this->getItemsRecursively(Item::TYPE_PERMISSION, $storage->getChildren($roleKey));
            foreach ($this->hydratePermissions($permissions, $roleKey) as $permission) {
                $allPermissions->push($permission);
            }
        });

        return $allPermissions;
    }

    public function getPlainForeignKey()
    {
        return 'parent_role_key';
    }
}
