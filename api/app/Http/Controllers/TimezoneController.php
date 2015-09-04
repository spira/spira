<?php

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Services\Datasets\Timezones;
use Spira\Responder\Response\ApiResponse;

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
        parent::__construct($transformer);
    }

    /**
     * Get all entities.
     *
     * @return ApiResponse
     */
    public function getAll()
    {
        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($this->timezones->all());
    }
}
