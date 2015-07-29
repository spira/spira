<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NotImplementedException extends HttpException
{
    /**
     * Create a new Not Implemented exception instance.
     *
     * @param string     $message
     * @param int        $code
     * @param Exception  $previous
     * @return void
     */
    public function __construct($message = 'Not Implemented.', $code = 0, Exception $previous = null)
    {
        parent::__construct(501, $message, $previous);
    }
}
