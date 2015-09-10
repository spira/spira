<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 07.09.15
 * Time: 16:39
 */

namespace App\Extensions\Controller;


use App\Exceptions\ForbiddenException;
use Spira\Auth\Access\Gate;


trait AuthorizesRequestsTrait
{
    /**
     * Authorize a given action against a set of arguments.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return void
     *
     * @throws ForbiddenException
     */
    public function authorize($ability, $arguments = [])
    {
        list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);

        if (! $this->getGate()->check($ability, $arguments)) {
            throw $this->createGateUnauthorizedException($ability, $arguments);
        }
    }

    /**
     * Authorize a given action for a user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|mixed  $user
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return void
     *
     * @throws ForbiddenException
     */
    public function authorizeForUser($user, $ability, $arguments = [])
    {
        list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);

        $result = $this->getGate()->forUser($user)->check($ability, $arguments);

        if (! $result) {
            throw $this->createGateUnauthorizedException($ability, $arguments);
        }
    }

    /**
     * Guesses the ability's name if it wasn't provided.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return array
     */
    protected function parseAbilityAndArguments($ability, $arguments)
    {
        if (is_string($ability)) {
            return [$ability, $arguments];
        }

        return [debug_backtrace(false, 3)[2]['function'], $ability];
    }

    /**
     * @return Gate
     */
    protected function getGate()
    {
        return app(Gate::GATE_NAME);
    }

    /**
     * Throw an unauthorized exception based on gate results.
     *
     * @param  string  $ability
     * @param  array  $arguments
     * @return ForbiddenException
     */
    protected function createGateUnauthorizedException($ability, $arguments)
    {
        return new ForbiddenException();
    }
}