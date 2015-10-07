<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Access;

use Spira\Contract\Exception\ForbiddenException;

trait AuthorizesRequestsTrait
{
    /**
     * Authorize a given action against a set of arguments.
     *
     * @param  mixed  $permission
     * @param  mixed|array  $arguments
     * @return void
     *
     * @throws ForbiddenException
     */
    public function authorize($permission, $arguments = [])
    {
        if (! $this->getGate()->check($permission, $arguments)) {
            throw $this->createGateUnauthorizedException($permission, $arguments);
        }
    }

    /**
     * @return Gate
     */
    public function getGate()
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
