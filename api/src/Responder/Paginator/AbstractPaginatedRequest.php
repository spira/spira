<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 30.07.15
 * Time: 14:46
 */

namespace Spira\Responder\Paginator;

use Illuminate\Http\Request;

abstract class AbstractPaginatedRequest extends Request
{
    /**
     * @param null|int $default
     * @return mixed
     */
    abstract public function getOffset($default = null);

    /**
     * @param null|int $default
     * @param null|int $max
     * @return mixed
     */
    abstract public function getLimit($default = null, $max = null);

    /**
     * states that limit should be applied to the end of the result set
     * @return bool
     */
    abstract public function isGetLast();

}