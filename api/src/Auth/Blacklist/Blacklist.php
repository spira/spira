<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Auth\Blacklist;

use Carbon\Carbon;
use Spira\Auth\Token\TokenExpiredException;

class Blacklist
{
    private $key;
    /**
     * @var StorageInterface
     */
    private $driver;
    private $exp;

    /**
     * @param StorageInterface $driver
     * @param string $key key of the token id in the payload
     * @param string|null $exp key of the exp inside payload
     */
    public function __construct(StorageInterface $driver, $key, $exp = null)
    {
        $this->key = $key;
        $this->driver = $driver;
        $this->exp = $exp;
    }

    /**
     * @param $payload
     * @return void
     */
    public function add($payload)
    {
        $seconds = null;
        if ($this->exp && isset($payload[$this->exp])) {
            $exp = Carbon::createFromTimeStampUTC($payload[$this->exp]);
            if ($exp->isPast()) {
                return;
            }

            $seconds = $exp->diffInSeconds(Carbon::now()->subSecond(10));
        }

        if (isset($payload[$this->key])) {
            $this->getDriver()->add($payload[$this->key], $seconds);
        }
    }

    /**
     * Checks if token in a blacklist.
     * @param $payload
     * @return bool
     * @throw TokenExpiredException
     */
    public function check($payload)
    {
        if (isset($payload[$this->key]) && $this->getDriver()->get($payload[$this->key])) {
            throw new TokenExpiredException;
        }

        return false;
    }

    /**
     * @return StorageInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }
}
