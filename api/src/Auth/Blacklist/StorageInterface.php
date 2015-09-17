<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 17.09.15
 * Time: 20:29
 */

namespace Spira\Auth\Blacklist;


interface StorageInterface
{
    /**
     * @param $id
     * @param $seconds
     * @return mixed
     */
    public function add($id, $seconds);

    /**
     * @param $id
     * @return mixed
     */
    public function get($id);
}