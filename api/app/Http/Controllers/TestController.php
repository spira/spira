<?php namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Repositories\TestRepository;

class TestController extends EntityController
{
    /**
     * Assign dependencies.
     * @param TestRepository $repository
     * @param EloquentModelTransformer $transformer
     */
    public function __construct(TestRepository $repository, EloquentModelTransformer $transformer)
    {
        parent::__construct($repository,$transformer);
    }

    public function urlEncode($id)
    {
        return $this->getResponse()->item(['test'=>$id]);
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
