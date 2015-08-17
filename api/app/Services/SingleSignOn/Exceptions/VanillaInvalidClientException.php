<?php

namespace App\Services\SingleSignOn\Exceptions;

class VanillaInvalidClientException extends VanillaException
{
    /**
     * The error type as expected by Vanilla.
     *
     * @var string
     */
    protected $type = 'invalid_client';
}
