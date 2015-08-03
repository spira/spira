<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
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
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $previous);
    }
}
