<?php namespace App\Http\Controllers;

use App\Models\TestEntity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\Request;
use App\Repositories\TestRepository;
use App\Http\Validators\TestValidator;

class TestController extends BaseController
{
    /**
     * Assign dependencies.
     * @param TestValidator $validator
     * @param TestRepository $repository
     */
    public function __construct(TestValidator $validator, TestRepository $repository)
    {
        $this->validator = $validator;
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
