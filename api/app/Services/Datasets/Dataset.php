<?php namespace App\Services\Datasets;

use ReflectionClass;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

abstract class Dataset
{
    /**
     * Cache repository.
     *
     * @var CacheRepository
     */
    protected $cache;

    /**
     * Assign dependencies.
     *
     * @param  CacheRepository  $cache
     * @return void
     */
    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get the dataset collection
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        $cacheKey = 'dataset'.(new ReflectionClass($this))->getShortName();

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $dataset = $this->cache->rememberForever($cacheKey, function () {
            return $this->getDataset();
        });

        return $dataset;
    }

    /**
     * Get the dataset.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract protected function getDataset();
}
