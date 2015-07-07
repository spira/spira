<?php namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UnprocessableEntityException extends HttpException
{
    /**
     * Create a new Unprocessable Entity exception instance.
     *
     * @param  string  $message
     * @param  \Exception $previous
     * @return void
     */
    public function __construct($message = 'Unprocessable Entity.', Exception $previous = null)
    {
        parent::__construct(422, $message, $previous);
    }
}
