<?php namespace App\Exceptions;

use Illuminate\Support\MessageBag;
use Illuminate\Http\Exception\HttpResponseException;

class ValidationException extends HttpResponseException
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
     * @return void
     */
    public function __construct(MessageBag $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Get the response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return response($this->errors, 422);
    }
}
