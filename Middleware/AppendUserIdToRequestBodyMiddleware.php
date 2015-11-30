<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spira\Core\Contract\Exception\UnauthorizedException;

class AppendUserIdToRequestBodyMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            throw new UnauthorizedException();
        }

        $request->merge(['user_id' => $user->user_id]);

        return $next($request);
    }
}
