<?php

namespace App\Http\Transformers;

use App\Services\TransformerService;
use League\Fractal\TransformerAbstract;
use Spira\Model\Collection\Collection;
use Spira\Responder\Contract\TransformerInterface;

abstract class BaseTransformer extends TransformerAbstract  implements TransformerInterface
{
    /**
     * @var TransformerService
     */
    private $service;

    public function __construct(TransformerService $service)
    {
        $this->service = $service;
    }

    /**
     * @param $object
     * @return mixed
     */
    abstract public function transform($object);

    /**
     * @return TransformerService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function transformCollection($collection)
    {
        if ($collection instanceof Collection) {
            $collection = $collection->all(); //remove the items marked as deleted
        }

        return $this->getService()->collection($collection, $this);
    }

    /**
     * @param $item
     * @return mixed
     */
    public function transformItem($item)
    {
        return $this->getService()->item($item, $this);
    }
}
