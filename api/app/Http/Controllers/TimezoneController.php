<?php namespace App\Http\Controllers;

use App\Models\Timezones;

class TimezoneController extends BaseController
{
    /**
     * Get all entities.
     *
     * @return Response
     */
    public function getAll()
    {
        return $this->collection(Timezones::getTimezones());
    }
}
