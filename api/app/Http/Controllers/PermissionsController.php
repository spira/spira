<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\PermissionsTransformer;
use Illuminate\Http\Request;
use Spira\Rbac\Item\Item;

class PermissionsController extends ApiController
{
    /**
     * Enable permissions checks.
     */
    protected $permissionsEnabled = true;

    protected $defaultRole = false;

    public function __construct(PermissionsTransformer $transformer)
    {
        parent::__construct($transformer);
    }

    public function getUserRoles(Request $request, $id)
    {
        $this->authorize(static::class.'@getUserRoles', ['model' => (object) ['user_id' => $id]]);

        $storage = $this->getGate()->getStorage();
        $defaultRolesKeys = $this->getGate()->getDefaultRoles();
        $customRolesKeys = array_keys($storage->getAssignments($id));
        $rolesKeys = array_unique(array_merge($defaultRolesKeys, $customRolesKeys));

        $roles = [];
        foreach ($rolesKeys as $roleKey) {
            $roles[$roleKey] = $storage->getItem($roleKey);
        }

        $roles = $this->getItemsRecursively(Item::TYPE_ROLE, $roles);

        $nested = $request->headers->get('With-Nested');
        if ($nested) {
            $requestedRelations = explode(', ', $nested);
            if (array_search('permissions', $requestedRelations) !== false) {
                foreach ($roles as $roleName => $role) {
                    $role->_permissions = $this->getItemsRecursively(Item::TYPE_PERMISSION, $storage->getChildren($roleName));
                }
            }
        }

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($roles);
    }

    protected function getItemsRecursively($type = Item::TYPE_ROLE, array $traversing = [], array &$items = [])
    {
        $storage = $this->getGate()->getStorage();
        /** @var Item[] $traversing */
        foreach ($traversing as $key => $item) {
            if (! isset($items[$key]) && $item->type === $type) {
                $items[$key] = $item;
            }
            $this->getItemsRecursively($type, $storage->getChildren($key), $items);
        }

        return $items;
    }
}
