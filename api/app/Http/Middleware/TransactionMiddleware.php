<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 10.08.15
 * Time: 17:05
 */

namespace App\Http\Middleware;

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
