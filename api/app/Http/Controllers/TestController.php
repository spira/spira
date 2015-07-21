<?php namespace App\Http\Controllers;

use App\Repositories\TestRepository;
use Spira\Responder\Contract\ApiResponderInterface;

class TestController extends ApiController
{
    /**
     * Assign dependencies.
     * @param TestRepository $repository
     * @param ApiResponderInterface $responder
     */
    public function __construct(TestRepository $repository, ApiResponderInterface $responder)
    {
        $this->repository = $repository;
        $this->responder = $responder;
    }

    /**
     * Test a standard internal exception.
     */
    public function internalException()
    {
        throw new \RuntimeException('Something went wrong');
    }

    /**
     * Test a fatal exception (has to be tested with guzzle to stop phpunit halting).
     *
     * @codeCoverageIgnore
     */
    public function fatalError()
    {
        call_to_non_existent_function();
    }
}
