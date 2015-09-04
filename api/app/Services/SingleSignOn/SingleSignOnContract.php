<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services\SingleSignOn;

interface SingleSignOnContract
{
    /**
     * Get the response to the requester.
     *
     * @return mixed
     */
    public function getResponse();
}
