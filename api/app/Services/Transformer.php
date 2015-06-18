<?php namespace App\Services;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Contracts\ArrayableInterface;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

class Transformer
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
     * @param  SerializerAbstract $serializer
     * @return void
     */
    public function __construct(SerializerAbstract $serializer)
    {
        $this->manager = new Manager();
        $this->manager->setSerializer($serializer);
    }

    /**
     * Parse Include String.
     *
     * @param array|string $includes Array or csv string of resources to include
     *
     * @return $this
     */
    public function parseIncludes($includes)
    {
        $this->manager->parseIncludes($includes);
    }

    /**
     * Create transformed data from a collection.
     *
     * @param  object  $data
     * @param  League\Fractal\TransformerAbstract  $transformer
     * @param  string  $resourceKey
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
     * @param  object  $data
     * @param  League\Fractal\TransformerAbstract  $transformer
     * @param  string  $resourceKey
     * @return array
     */
    public function item($data, $transformer = null, $resourceKey = null)
    {
        $resource = new Item($data, $this->getTransformer($transformer), $resourceKey);

        return $this->manager->createData($resource)->toArray();
    }

    /**
     * Create paginated transformed data from a collection.
     *
     * @param  Paginator  $paginator
     * @param  League\Fractal\TransformerAbstract  $transformer
     * @param  string  $resourceKey
     * @return array
     */
    public function paginatedCollection(Paginator $paginator, $transformer = null, $resourceKey = null)
    {
        $paginator->appends(\Request::query());

        $resource = new Collection($paginator->getCollection(), $this->getTransformer($transformer), $resourceKey);

        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        return $this->manager->createData($resource)->toArray();
    }

    /**
     * Get the transformer to use.
     *
     * @param  TransformerAbstract  $transformer
     * @return TransformerAbstract|callback
     */
    protected function getTransformer($transformer = null)
    {
        return $transformer ?: function($data) {

            if($data instanceof ArrayableInterface) {
                return $data->toArray();
            }

            return (array) $data;
        };
    }
}