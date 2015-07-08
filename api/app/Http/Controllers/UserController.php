<?php namespace App\Http\Controllers;

use App\Repositories\UserRepository as Repository;

class UserController extends BaseController
{
    /**
     * Assign dependencies.
     *
     * @param  Repository  $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }
}
