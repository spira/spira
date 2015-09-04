<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services\SingleSignOn\Exceptions;

use Exception;

abstract class VanillaException extends Exception
{
    /**
     * Get the type of error as expected by Vanilla.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
