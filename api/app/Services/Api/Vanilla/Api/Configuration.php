<?php

namespace App\Services\Api\Vanilla\Api;

class Configuration extends ApiAbstract
{
    /**
     * Request current configuration.
     *
     * @link https://github.com/kasperisager/vanilla-api/wiki/Endpoints#get-the-current-configuration
     *
     * @return object
     */
    public function current()
    {
        return $this->get('configuration');
    }
}
