<?php

namespace Spira\Auth\Access;

use App\Models\User;
use Spira\Model\Collection\Collection;

class Gate extends \Illuminate\Auth\Access\Gate
{

    const GATE_NAME = 'spira.gate';

    /**
     * Determine if the given ability should be granted for the current user.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function check($ability, $arguments = [])
    {
        if (! $user = $this->resolveUser()) {
            $user = new User();
        }

        $arguments = is_array($arguments) ? $arguments : [$arguments];

        if (! is_null($result = $this->callBeforeCallbacks($user, $ability, $arguments))) {
            return $result;
        }

        $callback = $this->resolveAuthCallback(
            $user, $ability, $arguments
        );

        return call_user_func_array($callback, array_merge([$user], $arguments));
    }

    /**
     * Determine if the first argument in the array corresponds to a policy.
     *
     * @param  array  $arguments
     * @return bool
     */
    protected function firstArgumentCorrespondsToPolicy(array $arguments)
    {
        if (isset($arguments[0]) && is_object($arguments[0])){
            $policyName = $this->getPolicyName($arguments[0]);
            return isset($this->policies[$policyName]);
        }

        return false;
    }

    /**
     * Resolve the callable for the given ability and arguments.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $ability
     * @param  array  $arguments
     * @return callable
     */
    protected function resolveAuthCallback($user, $ability, array $arguments)
    {
        if ($this->firstArgumentCorrespondsToPolicy($arguments)) {
            return $this->resolvePolicyCallback($user, $ability, $arguments);
        } elseif (is_string($ability) && isset($this->abilities[$ability])) {
            return $this->abilities[$ability];
        } else {
            return function () { return true; };
        }
    }

    /**
     * Resolve the callback for a policy check.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $ability
     * @param  array  $arguments
     * @return callable
     */
    protected function resolvePolicyCallback($user, $ability, array $arguments)
    {
        return function () use ($user, $ability, $arguments) {

            $instance = $this->resolvePolicy(
                $this->policies[$this->getPolicyName($arguments[0])]
            );

            if (method_exists($instance, 'before')) {
                // We will prepend the user and ability onto the arguments so that the before
                // callback can determine which ability is being called. Then we will call
                // into the policy before methods with the arguments and get the result.
                $beforeArguments = array_merge([$user, $ability], $arguments);

                $result = call_user_func_array(
                    [$instance, 'before'], $beforeArguments
                );

                // If we recieved a non-null result from the before method, we will return it
                // as the result of a check. This allows developers to override the checks
                // in the policy and return a result for all rules defined in the class.
                if (! is_null($result)) {
                    return $result;
                }
            }

            if (!method_exists($instance, $ability)){
                return true;
            }

            return call_user_func_array(
                [$instance, $ability], array_merge([$user], $arguments)
            );
        };
    }

    /**
     * @param $obj
     * @return string
     */
    protected function getPolicyName($obj)
    {
        if ($obj instanceof Collection){
            return  $obj->getClassName();
        }

        return get_class($obj);
    }
}