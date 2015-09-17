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
 * Date: 16.09.15
 * Time: 23:23.
 */

namespace Spira\Contract\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NotImplementedException extends HttpException
{
    /**
     * Create a new Not Implemented exception instance.
     *
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message = 'Not Implemented.', $code = 0, Exception $previous = null)
    {
        if ($code == 0) {
            $code = Response::HTTP_NOT_IMPLEMENTED;
        }
        parent::__construct($code, $message, $previous);
    }
}
