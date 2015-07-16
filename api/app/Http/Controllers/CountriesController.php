<?php namespace App\Http\Controllers;

use App\Services\Datasets\Countries;

class CountriesController extends BaseController
{
    /**
     * Assign dependencies.
     *
     * @param  Countries  $countries
     * @return void
     */
    public function __construct(Countries $countries)
    {
        $this->countries = $countries;
    }

    /**
     * Get all entities.
     *
     * @return Response
     */
    public function getAll()
    {
        return $this->collection($this->countries->all());
    }
}
