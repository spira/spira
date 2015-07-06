<?php namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    /**
     * Create a new Unauthorized exception instance.
     *
     * @param  string  $message
     * @return void
     */
    public function __construct($message = 'Unauthorized.')
    {
        parent::__construct(401, $message);
    }
}
