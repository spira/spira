<?php

namespace Spira\Rbac\Access;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Spira\Contract\Exception\NotImplementedException;
use Spira\Rbac\Item\Item;
use Spira\Rbac\Item\Rule;
use Spira\Rbac\Storage\StorageInterface;

class Gate implements GateContract
{
    /**
     * @var StorageInterface
     */
    private $storage;
    /**
     * @var Guard
     */
    private $guard;

    /**
     * @param StorageInterface $storage
     * @param Guard $guard
     */
    public function __construct(StorageInterface $storage, Guard $guard)
    {
        $this->storage = $storage;
        $this->guard = $guard;
    }

    /**
     * Determine if a given ability has been defined.
     *
     * @param  string $ability
     * @return bool
     */
    public function has($ability)
    {
        throw new NotImplementedException;
    }

    /**
     * Define a new ability.
     *
     * @param  string $ability
     * @param  callable|string $callback
     * @return GateContract
     */
    public function define($ability, $callback)
    {
        throw new NotImplementedException;
    }

    /**
     * Define a policy class for a given class type.
     *
     * @param  string $class
     * @param  string $policy
     * @return GateContract
     */
    public function policy($class, $policy)
    {
        throw new NotImplementedException;
    }

    /**
     * Determine if the given ability should be granted.
     *
     * @param  string $itemName
     * @param  array|mixed $arguments
     * @return bool
     */
    public function check($itemName, $arguments = [])
    {
        $user = $this->guard->user();

        if (!($userId = $user->getAuthIdentifier())){
            return false;
        }

        $assignments = $this->getStorage()->getAssignments($userId);
        return $this->checkAccessRecursive($user, $itemName, $arguments, $assignments);
    }

    protected function checkAccessRecursive($user, $itemName, $params, $assignments)
    {
        if (!($item = $this->getStorage()->getItem($itemName))){
            return true;
        }

        if (isset($assignments[$itemName]) && $this->executeRule($user, $item, $params)) {
            return true;
        }

        foreach ($this->getStorage()->getParentNames($itemName) as $parent) {
            if ($this->checkAccessRecursive($user, $parent, $params, $assignments)) {
                return true;
            }
        }

        return false;
    }

    protected function executeRule(Authenticatable $user, Item $item, $params)
    {
        if ($item->ruleName !== null){
            /** @var Rule $rule */
            $rule = new $item->ruleName();
            if (!$rule->execute($user, $params)){
                return false;
            }
        }

        return true;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }
}