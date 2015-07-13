<?php namespace App\Http\Controllers;

use App\Repositories\TestRepository;
use App\Http\Validators\TestEntityValidator;

class TestController extends BaseController
{
    /**
     * Assign dependencies.
     * @param TestRepository $repository
     */
    public function __construct(TestRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Test a standard internal exception
     */
    public function internalException()
    {
        throw new \RuntimeException("Something went wrong");
    }

    /**
     * Test a fatal exception (has to be tested with guzzle to stop phpunit halting)
     * @codeCoverageIgnore
     */
    public function fatalError()
    {
        call_to_non_existent_function();
    }


}
