<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 17.09.15
 * Time: 15:41.
 */

namespace Spira\Contract\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForbiddenException extends HttpException
{
    /**
     * Create a new Forbidden exception instance.
     *
     * @param  string $message
     * @param  int $code
     * @param  \Exception $previous
     */
    public function __construct($message = 'Denied.', $code = 0, Exception $previous = null)
    {
        if ($code == 0) {
            $code = Response::HTTP_FORBIDDEN;
        }
        parent::__construct($code, $message, $previous);
    }
}
