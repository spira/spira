<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class NotImplementedException extends \Spira\Contract\Exception\NotImplementedException
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
        parent::__construct($message, Response::HTTP_NOT_IMPLEMENTED, $previous);
    }
}
