<?php

namespace App\Exceptions;

use Illuminate\Support\MessageBag;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidationException extends HttpException
{
    /**
     * The validation error messages.
     *
     * @var \Illuminate\Support\MessageBag
     */
    protected $errors;

    /**
     * Create a new validation exception instance.
     *
     * @param  \Illuminate\Support\MessageBag
     *
     * @return void
     */
    public function __construct(MessageBag $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Returns the status code.
     *
     * @return int An HTTP response status code
     */
    public function getStatusCode()
    {
        return 422;
    }

    /**
     * Return the response instance.
     *
     * @return array
     */
    public function getResponse()
    {
        return [
            'message' => 'There was an issue with the validation of provided entity',
            'invalid' => $this->errors,
        ];
    }
}
