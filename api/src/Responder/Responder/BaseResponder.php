<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 1:08
 */

namespace Spira\Responder\Responder;


use Spira\Responder\Contract\ResponderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BaseResponder implements ResponderInterface
{
    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $statusCode
     *
     * @throws HttpException
     *
     */
    public function error($message, $statusCode)
    {
        throw new HttpException($statusCode, $message);
    }

    /**
     * Return a 404 not found error.
     *
     * @param string $message
     *
     * @throws HttpException
     *
     * @return void
     */
    public function errorNotFound($message = 'Not Found')
    {
        $this->error($message, 404);
    }
    /**
     * Return a 400 bad request error.
     *
     * @param string $message
     *
     * @throws HttpException
     *
     * @return void
     *
     */
    public function errorBadRequest($message = 'Bad Request')
    {
        $this->error($message, 400);
    }
    /**
     * Return a 403 forbidden error.
     *
     * @param string $message
     *
     * @throws HttpException
     *
     * @return void
     */
    public function errorForbidden($message = 'Forbidden')
    {
        $this->error($message, 403);
    }
    /**
     * Return a 500 internal server error.
     *
     * @param string $message
     *
     * @throws HttpException
     *
     * @return void
     */
    public function errorInternal($message = 'Internal Error')
    {
        $this->error($message, 500);
    }
    /**
     * Return a 401 unauthorized error.
     *
     * @param string $message
     *
     * @throws HttpException
     *
     * @return void
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        $this->error($message, 401);
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return new Response();
    }
}