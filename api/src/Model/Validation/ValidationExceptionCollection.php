<?php

namespace Spira\Model\Validation;

use Spira\Responder\Contract\TransformableInterface;
use Spira\Responder\Contract\TransformerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidationExceptionCollection extends HttpException implements TransformableInterface
{
    /**
     * @var ValidationException[]
     */
    private $exceptions;

    public function __construct(array $exceptions)
    {
        $this->exceptions = $exceptions;
    }

    /**
     * Returns the status code.
     *
     * @return int An HTTP response status code
     */
    public function getStatusCode()
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    /**
     * Return the response instance.
     *
     * @return array
     */
    public function getResponse()
    {
        $responses = [];
        foreach ($this->exceptions as $exception) {
            if (! is_null($exception)) {
                $responses[] = $exception->getErrors();
            } else {
                $responses[] = null;
            }
        }

        return $responses;
    }

    /**
     * @param TransformerInterface $transformer
     * @return mixed
     */
    public function transform(TransformerInterface $transformer)
    {
        return [
            'message' => 'There was an issue with the validation of provided entity',
            'invalid' => $transformer->transformCollection($this->getResponse()),
        ];
    }
}
