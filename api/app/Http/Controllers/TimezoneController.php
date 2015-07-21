<?php namespace App\Http\Controllers;

use App\Services\Datasets\Timezones;
use Spira\Responder\Contract\ApiResponderInterface;

class TimezoneController extends ApiController
{
    /**
     * Assign dependencies.
     *
     * @param  Timezones              $timezones
     * @param  ApiResponderInterface  $timezones
     * @return void
     */
    public function __construct(Timezones $timezones, ApiResponderInterface $responder)
    {
        $this->timezones = $timezones;
        $this->responder = $responder;
    }

    /**
     * Get all entities.
     *
     * @return Response
     */
    public function getAll()
    {
        return $this->getResponder()->collection($this->timezones->all());
    }
}
