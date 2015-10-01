<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Access;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Spira\Contract\Exception\NotImplementedException;
use Spira\Rbac\Item\Item;
use Spira\Rbac\Item\Rule;
use Spira\Rbac\Storage\StorageInterface;

class Gate implements GateContract
{
    const GATE_NAME = 'spira.rbac';

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var callable
     */
    private $userResolver;

    /**
     * @var array
     */
    private $defaultRoles;

    protected static $itemCache = [];

    protected static $ruleCache = [];

    /**
     * @param StorageInterface $storage
     * @param callable $userResolver
     * @param array $defaultRoles
     */
    public function __construct(StorageInterface $storage, callable $userResolver, array $defaultRoles = [])
    {
        $this->storage = $storage;

        $this->userResolver = function () use ($userResolver) {

            $user = call_user_func($userResolver);
            if (! $user) {
                throw new UserNotFoundException;
            }

            return $user;
        };

        $this->defaultRoles = $defaultRoles;
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
        if (! ($item = $this->getItem($itemName))) {
            return true;
        }

        try {
            //default roles check
            if (! empty($this->defaultRoles) && $this->checkAccessRecursive($itemName, $arguments)) {
                return true;
            }

            $user = $this->resolveUser();
        } catch (UserNotFoundException $e) {
            return false;
        }

        $assignments = $this->getStorage()->getAssignments($user->getAuthIdentifier());

        return $this->checkAccessRecursive($itemName, $arguments, $assignments);
    }

    /**
     * Get Gate service for particular user.
     *
     * @param Authenticatable $user
     * @return static
     */
    public function forUser(Authenticatable $user)
    {
        return new static($this->getStorage(), function () use ($user) {return $user;}, $this->defaultRoles);
    }

    /**
     * Check permissions and roles recursively.
     *
     * @param $itemName
     * @param $params
     * @param array $assignments
     * @param array $itemStack
     * @return bool
     */
    protected function checkAccessRecursive($itemName, $params, $assignments = [], $itemStack = [])
    {
        if (! ($item = $this->getItem($itemName))) {
            return false;
        }

        $itemStack[$item->name] = $item;

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            foreach ($itemStack as $itemName => $item) {
                if (! $this->executeRule($item, $params)) {
                    return false;
                }
            }

            return true;
        }

        foreach ($this->getStorage()->getParentNames($itemName) as $parent) {
            if ($this->checkAccessRecursive($parent, $params, $assignments, $itemStack)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $itemName
     * @return Item|false
     */
    protected function getItem($itemName)
    {
        if (! isset(static::$itemCache[$itemName])) {
            static::$itemCache[$itemName] = $this->getStorage()->getItem($itemName);
        }

        return static::$itemCache[$itemName];
    }

    /**
     * @param Item $item
     * @param $params
     * @return bool
     */
    protected function executeRule(Item $item, $params)
    {
        if ($item->getRuleName() !== null) {
            $rule = $this->getRule($item->getRuleName());
            if (! $rule->execute($this->userResolver, $params)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $ruleName
     * @return Rule
     */
    protected function getRule($ruleName)
    {
        if (! isset(static::$ruleCache[$ruleName])) {
            static::$ruleCache[$ruleName] = new $ruleName;
        }

        return static::$ruleCache[$ruleName];
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Resolve the user from the user resolver.
     *
     * @return Authenticatable
     * @throw UserNotFoundException
     */
    protected function resolveUser()
    {
        return call_user_func($this->userResolver);
    }
}
