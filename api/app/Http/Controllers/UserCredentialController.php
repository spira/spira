<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\User;
use Illuminate\Http\Request;
use Spira\Auth\Driver\Guard;

class UserCredentialController extends ChildEntityController
{

    /** @var Guard $auth */
    protected $auth;

    protected $relationName = 'userCredential';

    public function __construct(User $parentModel, EloquentModelTransformer $transformer, Guard $auth)
    {
        parent::__construct($parentModel, $transformer);
        $this->auth = $auth;
    }


    /**
     * @param Request $request
     * @param string $id
     * @param bool|false $childId
     * @return $this
     */
    public function patchOne(Request $request, $id, $childId = false)
    {
        $response = parent::patchOne($request, $id, $childId);

        if ($id == $request->user()->getKey()){

            /**
             * When the credentials are updated by the user, invalidate the old token.
             * If this action isn't completed by the owner we can't invalidate the owners token as it isn't related
             * to this request.
             */
            $this->auth->setRequest($request)->logout();

            //then send a new valid token
            $response->header('Authorization-Update', $this->auth->generateToken($request->user()));
        }

        return $response;
    }

}
