<?php namespace App\Http\Controllers;

use App\Http\Validators\TestEntityValidator;
use App\Repositories\UserRepository;

class UserController extends BaseController
{

    /**
     * Assign dependencies.
     * @param TestEntityValidator $validator
     * @param UserRepository $repository
     */
    public function __construct(TestEntityValidator $validator, UserRepository $repository)
    {
        $this->validator = $validator;
        $this->repository = $repository;
    }

}
