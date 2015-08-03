<?php namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Services\Datasets\Timezones;

class TimezoneController extends ApiController
{
    /**
     * Assign dependencies.
     *
     * @param Timezones $timezones
     * @param EloquentModelTransformer $transformer
     */
    public function __construct(Timezones $timezones, EloquentModelTransformer $transformer)
    {
        $this->timezones = $timezones;
        $this->transformer = $transformer;
    }

    /**
     * Get all entities.
     *
     * @return Response
     */
    public function getAll()
    {
        return $this->getResponse()->collection($this->timezones->all(), $this->transformer);
    }
}
