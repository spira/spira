<?php

namespace App\Extensions\Socialite\Contracts;

interface Provider
{
    /**
     * Constant representing the cache replacing session time to live in min.
     *
     * @var int
     */
    const CACHE_TTL = 30;
}
