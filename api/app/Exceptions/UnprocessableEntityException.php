<?php namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnprocessableEntityException extends HttpException
{
    /**
     * Create a new Unprocessable Entity exception instance.
     *
     * @param  string  $message
     * @return void
     */
    public function __construct($message = 'Unprocessable Entity.')
    {
        parent::__construct(422, $message);
    }
}
