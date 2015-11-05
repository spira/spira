<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\User;

use Illuminate\Contracts\Auth\Authenticatable;
use Spira\Rbac\Access\UserNotFoundException;

class UserProxy
{
    /**
     * @var \Closure
     */
    private $userResolver;

    public function __construct(\Closure $userResolver)
    {
        $this->userResolver = $userResolver;
    }

    /**
     * @return Authenticatable
     * @throws UserNotFoundException
     */
    public function resolveUser()
    {
        $user = call_user_func($this->userResolver);
        if (! $user) {
            throw new UserNotFoundException;
        }

        return $user;
    }
}
