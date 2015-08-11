<?php

namespace App\Services\SingleSignOn\Exceptions;

class VanillaAccessDeniedException extends VanillaException
{
    /**
     * The error type as expected by Vanilla.
     *
     * @var string
     */
    protected $type = 'access_denied';
}
