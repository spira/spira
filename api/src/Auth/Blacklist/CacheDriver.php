<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 17.09.15
 * Time: 20:42
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
    public function add($id, $seconds)
    {
        $this->cache->add($id,$id,ceil($seconds/60));
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