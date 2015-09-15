<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 16.09.15
 * Time: 0:20
 */

namespace Spira\Auth\Token;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TokenExpiredException extends HttpException
{
    /**
     * Create a new Token Invalid exception instance.
     *
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     *
     */
    public function __construct($message = 'Token has expired', $code = 0, Exception $previous = null)
    {
        parent::__construct(Response::HTTP_UNAUTHORIZED, $message, $previous);
    }
}