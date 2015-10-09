<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Auth\Blacklist;

use Illuminate\Contracts\Cache\Repository;

class CacheDriver implements StorageInterface
{
    /**
     * @var Repository
     */
    private $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param $id
     * @param $seconds
     * @return mixed
     */
    public function add($id, $seconds = null)
    {
        if (!$seconds) {
            $seconds = 60 * 60;
        }

        $this->cache->add($id, $id, ceil($seconds / 60));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->cache->get($id);
    }
}
