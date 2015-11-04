<?php

namespace App\Models\Relations;

use App\Models\Role;
use App\Models\RoleCollection;
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

    public function get($columns = ['*'])
    {
        $storage = $this->getGate()->getStorage();
        $defaultRolesKeys = $this->getGate()->getDefaultRoles();
        $customRolesKeys =$this->query->getQuery()->lists($this->otherKey);
        $rolesKeys = array_unique(array_merge($defaultRolesKeys, $customRolesKeys));

        $roles = [];
        foreach ($rolesKeys as $roleKey) {
            $roles[$roleKey] = $storage->getItem($roleKey);
        }

        $roles = $this->getItemsRecursively(Item::TYPE_ROLE, $roles);

        return new RoleCollection($this->hydrateRoles($roles, $defaultRolesKeys));
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
                'is_default' => in_array($role->name, $defaultRolesKeys)
            ]);
        }

        return $roleModels;
    }

}