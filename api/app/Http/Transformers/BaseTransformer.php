<?php namespace App\Http\Transformers;

use App\Services\TransformerService;
use League\Fractal\TransformerAbstract;

abstract class BaseTransformer extends TransformerAbstract
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
}
