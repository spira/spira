<?php namespace App\Http\Controllers;

use RuntimeException;
use App\Models\AuthToken;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use App\Exceptions\UnauthorizedException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends BaseController
{
    /**
     * JWT Auth
     *
     * @var Tymon\JWTAuth\JWTAuth
     */
    protected $jwtAuth;

    /**
     * Transformer to use for responses.
     *
     * @var string
     */
    protected $transformer = 'App\Http\Transformers\AuthTransformer';

    /**
     * Assign dependencies.
     *
     * @param  JWTAuth  $jwtAuth
     * @return void
     */
    public function __construct(JWTAuth $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
    }

    /**
     * Get a login token.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
        $credentials = [
            'email' => $request->getUser(),
            'password' => $request->getPassword()
        ];

        try {
            // Attempt to verify the credentials and create a token for the user
            if (!$token = $this->jwtAuth->attempt($credentials)) {

                throw new UnauthorizedException;
            }
        } catch (JWTException $e) {
            // Something went wrong whilst attempting to encode the token
            throw new RuntimeException('Token could not be encoded.');
        }

        // All good so return the token
        return $this->item(new AuthToken($token, $this->jwtAuth));
    }
}
