<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services\Api\Vanilla\Api;

class Configuration extends ApiAbstract
{
    /**
     * Request current configuration.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#get-the-current-configuration
     *
     * @return array
     */
    public function current()
    {
        return $this->get('configuration');
    }
}
