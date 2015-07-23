<?php namespace App\Http\Controllers;

use App\Services\Datasets\Countries;
use Spira\Responder\Contract\ApiResponderInterface;

class CountriesController extends ApiController
{
    /**
     * Assign dependencies.
     *
     * @param  Countries              $countries
     * @param  ApiResponderInterface  $responder
     * @return void
     */
    public function __construct(Countries $countries, ApiResponderInterface $responder)
    {
        $this->countries = $countries;
        $this->responder = $responder;
    }

    /**
     * Get all entities.
     *
     * @return Response
     */
    public function getAll()
    {
        return $this->getResponder()->collection($this->countries->all());
    }
}
