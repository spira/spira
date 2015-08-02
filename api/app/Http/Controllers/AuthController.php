<?php

namespace App\Http\Controllers;

use App;
use RuntimeException;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Contracts\Auth\Guard as Auth;
use App\Http\Transformers\AuthTokenTransformer;

class AuthController extends ApiController
{
    /**
     * JWT Auth Package.
     *
     * @var JWTAuth
     */
    protected $jwtAuth;

    /**
     * Illuminate Auth.
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Assign dependencies.
     *
     * @param  Auth                   $auth
     * @param  JWTAuth                $jwtAuth
     * @param  AuthTokenTransformer  $transformer
     */
    public function __construct(Auth $auth, JWTAuth $jwtAuth, AuthTokenTransformer $transformer)
    {
        $this->auth = $auth;
        $this->jwtAuth = $jwtAuth;
        $this->transformer = $transformer;
    }

    /**
     * Get a login token.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function login(Request $request)
    {
        $credentials = [
            'email'    => $request->getUser(),
            'password' => $request->getPassword(),
        ];

        if (!$this->auth->attempt($credentials)) {
            throw new UnauthorizedException('Credentials failed.');
        }

        try {
            $token = $this->jwtAuth->fromUser($this->auth->user());
        } catch (JWTException $e) {
            throw new RuntimeException($e->getMessage(), 500, $e);
        }

        return $this->getResponse()->item($token,$this->transformer);
    }

    /**
     * Refresh a login token.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function refresh(Request $request)
    {
        $token = $this->jwtAuth->getTokenFromRequest();

        // Get the user to make sure the token is fully valid
        $this->jwtAuth->getUser();

        $token = $this->jwtAuth->refresh($token);

        return $this->getResponse()->item($token,$this->transformer);
    }

    /**
     * Login with a single use token.
     *
     * @param Request                          $request
     * @param \App\Repositories\UserRepository $userRepository
     *
     * @return Response
     */
    public function token(Request $request, UserRepository $userRepository)
    {
        $header = $request->headers->get('authorization');
        if (!starts_with(strtolower($header), 'token')) {
            throw new BadRequestException('Single use token not provided.');
        }

        $token = trim(substr($header, 5));

        // If we didn't find the user, it was an expired/invalid token. No access granted
        if (!$user = $userRepository->findByLoginToken($token)) {
            throw new UnauthorizedException('Token invalid.');
        }

        $token = $this->jwtAuth->fromUser($user);

        return $this->getResponse()->item($token,$this->transformer);
    }
}
