<?php

namespace App\Http\Controllers;

use RuntimeException;
use App\Models\AuthToken;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Spira\Responder\Contract\ApiResponderInterface;

class AuthController extends ApiController
{
    /**
     * JWT Auth.
     *
     * @var JWTAuth
     */
    protected $jwtAuth;

    /**
     * Assign dependencies.
     *
     * @param  JWTAuth                $jwtAuth
     * @param  ApiResponderInterface  $responder
     */
    public function __construct(JWTAuth $jwtAuth, ApiResponderInterface $responder)
    {
        $this->jwtAuth = $jwtAuth;
        $this->responder = $responder;
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

        try {
            if (!$token = $this->jwtAuth->attempt($credentials)) {
                throw new UnauthorizedException('Credentials failed.');
            }
        } catch (JWTException $e) {
            throw new RuntimeException($e->getMessage(), 500, $e);
        }

        return $this->getResponder()->item(new AuthToken(['token' => $token]));
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
        return $this->getResponder()->item(new AuthToken(['token' => $token]));
    }

    /**
     * Login with a single use token.
     *
     * @param Request                          $request
     * @param \App\Repositories\UserRepository $user
     *
     * @return Response
     */
    public function token(Request $request, UserRepository $user)
    {
        $header = $request->headers->get('authorization');
        if (!starts_with(strtolower($header), 'token')) {
            throw new BadRequestException('Single use token not provided.');
        }

        $token = trim(substr($header, 5));

        // If we didn't find the user, it was an expired/invalid token. No access granted
        if (!$user = $user->findByLoginToken($token)) {
            throw new UnauthorizedException('Token invalid.');
        }

        $token = $this->jwtAuth->fromUser($user);

        return $this->getResponder()->item(new AuthToken(['token' => $token]));
    }
}
