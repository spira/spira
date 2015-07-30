<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 30.07.15
 * Time: 14:46
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
     * states that limit should be applied to the end of the result set
     * @return bool
     */
    public function isGetLast();

    /**
     * @return Request
     */
    public function getRequest();

}