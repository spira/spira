<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TokenInvalidException extends HttpException
{
    /**
     * Create a new Token Invalid exception instance.
     *
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     *
     * @return void
     */
    public function __construct($message = 'Invalid token.', $code = 0, Exception $previous = null)
    {
        parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, $message, $previous);
    }
}
