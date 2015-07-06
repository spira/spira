<?php namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class BadRequestException extends HttpException
{
    /**
     * Create a new Bad Request exception instance.
     *
     * @param  string  $message
     * @return void
     */
    public function __construct($message = 'Bad Request.')
    {
        parent::__construct(400, $message);
    }
}
