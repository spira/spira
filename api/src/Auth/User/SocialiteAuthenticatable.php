<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Auth\User;

interface SocialiteAuthenticatable
{
    /**
     * @param string $method
     * @return void
     */
    public function setCurrentAuthMethod($method);

    /**
     * @return string
     */
    public function getCurrentAuthMethod();
}
