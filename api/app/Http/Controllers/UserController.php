<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserRepository as Repository;
use App\Http\Validators\UserValidator as Validator;

class UserController extends BaseController
{
    /**
     * Assign dependencies.
     *
     * @param  Repository  $repository
     * @param  Validator   $validator
     * @return void
     */
    public function __construct(Repository $repository, Validator $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * Put an entity.
     *
     * @param  string   $id
     * @param  Request  $request
     * @return Response
     */
    public function putOne($id, Request $request)
    {
        parent::putOne($id, $request);
        return response(null, 204);
    }
}
