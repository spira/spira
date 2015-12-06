<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\Relations;

use Spira\Rbac\Access\Gate;
use Spira\Rbac\Item\Item;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

trait GateTrait
{
    protected function getItemsRecursively($type = Item::TYPE_ROLE, array $traversing = [], array &$items = [])
    {
        $storage = $this->getGate()->getStorage();
        /** @var Item[] $traversing */
        foreach ($traversing as $key => $item) {
            if ($item && (! isset($items[$key])) && $item->type === $type) {
                $items[$key] = $item;
            }
            $this->getItemsRecursively($type, $storage->getChildren($key), $items);
        }

        return $items;
    }

    /**
     * @return Gate
     */
    public function getGate()
    {
        return app(GateContract::class);
    }
}
