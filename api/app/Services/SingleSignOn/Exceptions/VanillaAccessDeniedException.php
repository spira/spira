<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services\SingleSignOn\Exceptions;

class VanillaAccessDeniedException extends VanillaException
{
    /**
     * The error type as expected by Vanilla.
     *
     * @var string
     */
    protected $type = 'access_denied';
}
