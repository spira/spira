<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 15.09.15
 * Time: 15:01
 */

namespace Spira\Auth\Token;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TokenIsMissingException extends HttpException
{
    public function __construct($message = 'The token can not be parsed from the Request', $code = 0, Exception $previous = null)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message, $previous);
    }
}