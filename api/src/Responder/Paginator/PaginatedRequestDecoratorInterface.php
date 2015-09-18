<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Responder\Paginator;

use Illuminate\Http\Request;

interface PaginatedRequestDecoratorInterface
{
    /**
     * @param null|int $default
     * @return mixed
     */
    public function getOffset($default = null);

    /**
     * @param null|int $default
     * @param null|int $max
     * @return mixed
     */
    public function getLimit($default = null, $max = null);

    /**
     * states that limit should be applied to the end of the result set.
     * @return bool
     */
    public function isGetLast();

    /**
     * @return Request
     */
    public function getRequest();
}
