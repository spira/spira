<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 17.07.15
 * Time: 20:12
 */

namespace App\Exceptions;

use HttpException;
use Spira\Responder\Contract\TransformableInterface;
use Spira\Responder\Contract\TransformerInterface;

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
        return 422;
    }

    /**
     * Return the response instance.
     *
     * @return array
     */
    public function getResponse()
    {
        $responses = [];
        foreach ($this->exceptions as $exception)
        {
            $responses[] = $exception->getResponse();
        }

        return $responses;
    }

    /**
     * @param TransformerInterface $transformer
     * @return mixed
     */
    public function transform(TransformerInterface $transformer)
    {
        return $transformer->transformCollection($this->getResponse());
    }
}