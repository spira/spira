<?php namespace App\Http\Controllers;

use RuntimeException;
use App\Models\AuthToken;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Exceptions\UnauthorizedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthController extends BaseController
{
    /**
     * JWT Auth
     *
     * @var Tymon\JWTAuth\JWTAuth
     */
    protected $jwtAuth;

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
            if (!$token = $this->jwtAuth->attempt($credentials)) {

                throw new UnauthorizedException;
            }
        } catch (JWTException $e) {
            throw new RuntimeException('Token could not be encoded.');
        }

        return $this->item(new AuthToken(['token' => $token]));
    }

    /**
     * Refresh a login token.
     *
     * @param  Request  $request
     * @return Response
     */
    public function refresh(Request $request)
    {
        if (!$token = $this->jwtAuth->setRequest($request)->getToken()) {
            throw new RuntimeException('Token not provided.');
        }

        try {
            $user = $this->jwtAuth->authenticate((string) $token);
        }
        catch (TokenExpiredException $e) {
            throw new UnauthorizedException;
        }
        catch (JWTException $e) {
            throw new RuntimeException('Token is invalid.');
        }

        if (!$user) {
            throw new RuntimeException('The user does not exist.');
        }

        $token = $this->jwtAuth->refresh($token);
        return $this->item(new AuthToken(['token' => $token]));
    }
}
