<?php

namespace App\Services\SingleSignOn\Exceptions;

class VanillaInvalidRequestException extends VanillaException
{
    /**
     * The error type as expected by Vanilla.
     *
     * @var string
     */
    protected $type = 'invalid_request';
}
