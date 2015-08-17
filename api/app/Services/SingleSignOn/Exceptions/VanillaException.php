<?php

namespace App\Services\SingleSignOn\Exceptions;

use \Exception;

abstract class VanillaException extends Exception
{
    /**
     * Get the type of error as expected by Vanilla.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
