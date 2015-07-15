<?php namespace App\Http\Controllers;

use App\Services\Timezones;

class TimezoneController extends BaseController
{
    /**
     * Assign dependencies.
     *
     * @param  Timezones  $timezones
     * @return void
     */
    public function __construct(Timezones $timezones)
    {
        $this->timezones = $timezones;
    }

    /**
     * Get all entities.
     *
     * @return Response
     */
    public function getAll()
    {
        return $this->collection($this->timezones->all());
    }
}
