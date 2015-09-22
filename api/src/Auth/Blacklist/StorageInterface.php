<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Auth\Blacklist;

interface StorageInterface
{
    /**
     * @param $id
     * @param $seconds
     * @return mixed
     */
    public function add($id, $seconds = null);

    /**
     * @param $id
     * @return mixed
     */
    public function get($id);
}
