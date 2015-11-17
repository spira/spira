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
use App\Models\RoleCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spira\Rbac\Item\Item;

class UserRoleRelation extends BelongsToMany
{
    use GateTrait;

    public function addConstraints()
    {
        $this->query->getQuery()->from($this->table);

        if (static::$constraints) {
            $this->setWhere();
        }
    }

    public function getRelated()
    {
        return new Role();
    }

    public function sync($ids, $detaching = true)
    {
        foreach ($this->getGate()->getDefaultRoles() as $defaultRole) {
            if (isset($ids[$defaultRole])) {
                unset($ids[$defaultRole]);
            }
        }

        return parent::sync($ids, $detaching);
    }

    public function get($columns = ['*'])
    {
        $roleKeys = $this->query->getQuery()->lists($this->otherKey);

        return $this->hydrateRolesByKeys($roleKeys);
    }

    public function getEager()
    {
        return new Collection($this->query->getQuery()->get());
    }

    public function buildDictionary(Collection $results)
    {
        $dictionary = [];
        foreach ($results as $result) {
            $dictionary[$result->user_id][] = $result->role_key;
        }

        foreach ($dictionary as &$item) {
            $item = $this->hydrateRolesByKeys($item)->all();
        }

        return $dictionary;
    }

    /**
     * @param array $roleKeys
     * @return Collection
     */
    protected function hydrateRolesByKeys(array $roleKeys)
    {
        $storage = $this->getGate()->getStorage();
        $defaultRolesKeys = $this->getGate()->getDefaultRoles();
        $rolesKeys = array_unique(array_merge($defaultRolesKeys, $roleKeys));

        $roles = [];
        foreach ($rolesKeys as $roleKey) {
            $roles[$roleKey] = $storage->getItem($roleKey);
        }

        $roles = $this->getItemsRecursively(Item::TYPE_ROLE, $roles);

        $roleModels = [];
        foreach ($roles as $role) {
            $roleModels[] = new Role([
                'key' => $role->name,
                'description' => $role->description,
                'is_default' => in_array($role->name, $defaultRolesKeys),
            ]);
        }

        $collection = new RoleCollection($roleModels);
        $collection->load(array_keys($this->query->getEagerLoads()));

        return $collection;
    }
}
