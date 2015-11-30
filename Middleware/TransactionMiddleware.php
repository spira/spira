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
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Http\Request;

class TransactionMiddleware
{
    /**
     * @var ConnectionResolverInterface
     */
    private $connectionResolver;

    public function __construct(ConnectionResolverInterface $connectionResolver)
    {
        $this->connectionResolver = $connectionResolver;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $this->connectionResolver->connection()->beginTransaction();
        try {
            $response = $next($request);
        } catch (\Exception $e) {
            $this->connectionResolver->connection()->rollBack();
            throw $e;
        }

        $this->connectionResolver->connection()->commit();

        return $response;
    }
}
