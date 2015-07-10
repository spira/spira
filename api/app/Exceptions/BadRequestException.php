<?php

namespace app\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BadRequestException extends HttpException
{
    /**
     * Create a new Bad Request exception instance.
     *
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     *
     * @return void
     */
    public function __construct($message = 'Bad Request.', $code = 0, Exception $previous = null)
    {
        parent::__construct(400, $message, $previous);
    }
}
