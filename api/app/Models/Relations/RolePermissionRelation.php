<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\Relations;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spira\Model\Collection\Collection;
use Spira\Rbac\Item\Item;

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
    }

    public function getResults()
    {
        $storage = $this->getGate()->getStorage();

        $permissions = $this->getItemsRecursively(Item::TYPE_PERMISSION, $storage->getChildren($this->roleKey));

        return new Collection($this->hydratePermissions($permissions));
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
}
