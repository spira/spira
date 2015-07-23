<?php namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForbiddenException extends HttpException
{
    /**
     * Create a new Forbidden exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Exception $previous
     * @return void
     */
    public function __construct($message = 'Denied.', $code = 0, Exception $previous = null)
    {
        parent::__construct(403, $message, $previous);
    }
}
