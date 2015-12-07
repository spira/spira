<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\RoleTransformer;
use App\Models\Role;
use Spira\Core\Controllers\EntityController;
use Spira\Core\Model\Collection\Collection;
use Spira\Rbac\Item\Item;

class RoleController extends EntityController
{
    public function __construct(Role $model, RoleTransformer $transformer)
    {
        parent::__construct($model, $transformer);
    }

    /**
     * @param null $limit
     * @param null $offset
     * @return Collection
     */
    protected function getAllEntities($limit = null, $offset = null)
    {
        $items = $this->getGate()->getStorage()->getItems(Item::TYPE_ROLE);
        $defaultRolesKeys = $this->getGate()->getDefaultRoles();

        return new Collection($this->hydrateRoles($items, $defaultRolesKeys));
    }

    /**
     * @param Item[] $roles
     * @param array $defaultRolesKeys
     * @return array
     */
    protected function hydrateRoles($roles, array $defaultRolesKeys)
    {
        $roleModels = [];
        foreach ($roles as $role) {
            $roleModels[] = new Role([
                'key' => $role->name,
                'description' => $role->description,
                'is_default' => in_array($role->name, $defaultRolesKeys),
            ]);
        }

        return $roleModels;
    }
}
