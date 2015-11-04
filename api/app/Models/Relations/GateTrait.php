<?php

namespace App\Models\Relations;

use Spira\Rbac\Access\Gate;
use Spira\Rbac\Item\Item;

trait GateTrait
{
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

    /**
     * @return Gate
     */
    public function getGate()
    {
        return app(Gate::GATE_NAME);
    }
}