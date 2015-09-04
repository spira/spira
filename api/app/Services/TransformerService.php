<?php

namespace App\Services;

use Illuminate\Contracts\Support\Arrayable;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

class TransformerService
{
    /**
     * Fractal manager.
     *
     * @var \League\Fractal\Manager
     */
    protected $manager;

    /**
     * Initialize the transform manager.
     *
     * @param SerializerAbstract $serializer
     * @param Manager $manager
     */
    public function __construct(SerializerAbstract $serializer, Manager $manager)
    {
        $this->manager = $manager;
        $this->manager->setSerializer($serializer);
    }

    /**
     * Create transformed data from a collection.
     *
     * @param object|array                       $data
     * @param TransformerAbstract $transformer
     * @param string                             $resourceKey
     *
     * @return array
     */
    public function collection($data, $transformer = null, $resourceKey = null)
    {
        $resource = new Collection($data, $this->getTransformer($transformer), $resourceKey);

        return $this->manager->createData($resource)->toArray()['data'];
    }

    /**
     * Create transformed data from an item.
     *
     * @param object                             $data
     * @param TransformerAbstract $transformer
     * @param string                             $resourceKey
     *
     * @return array
     */
    public function item($data, $transformer = null, $resourceKey = null)
    {
        $resource = new Item($data, $this->getTransformer($transformer), $resourceKey);

        return $this->manager->createData($resource)->toArray();
    }

    /**
     * Get the transformer to use.
     *
     * @param TransformerAbstract $transformer
     *
     * @return TransformerAbstract|callback
     */
    protected function getTransformer($transformer = null)
    {
        return $transformer ?: function ($data) {

            if ($data instanceof Arrayable) {
                return $data->toArray();
            }

            return (array) $data;
        };
    }
}
