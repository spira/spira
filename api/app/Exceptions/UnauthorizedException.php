<?php namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    /**
     * Create a new Unauthorized exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Exception $previous
     * @return void
     */
    public function __construct($message = 'Unauthorized.', $code = 0, Exception $previous = null)
    {
        parent::__construct(401, $message, $previous);
    }
}
