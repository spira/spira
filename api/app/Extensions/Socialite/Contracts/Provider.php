<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Socialite\Contracts;

interface Provider
{
    /**
     * Constant representing the cache replacing session time to live in min.
     *
     * @var int
     */
    const CACHE_TTL = 30;

    /**
     * Get the return url for the request.
     *
     * @return string
     */
    public function getCachedReturnUrl();
}
