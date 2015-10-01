<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Spira\Rbac\Item\Rule;

/**
 * Checks if authorID matches userID passed via params.
 */
class AuthorRule extends Rule
{
    public $name = 'isAuthor';
    public $reallyReally = false;

    /**
     * {@inheritdoc}
     */
    public function execute(callable $userResolver, $params)
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = $userResolver();

        return $params['authorID'] == $user->getAuthIdentifier();
    }
}
