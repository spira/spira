<?php namespace App\Http\Controllers;

use RuntimeException;
use App\Models\AuthToken;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Exceptions\UnprocessableEntityException;
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

                throw new UnauthorizedException('Credentials failed.');
            }
        } catch (JWTException $e) {
            throw new RuntimeException($e->getMessage(), 500, $e);
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
            throw new BadRequestException('Token not provided.');
        }

        try {
            $user = $this->jwtAuth->authenticate((string) $token);
        }
        catch (TokenExpiredException $e) {
            throw new UnauthorizedException('Token expired.');
        }
        catch (JWTException $e) {
            throw new UnprocessableEntityException($e->getMessage(), $e);
        }

        if (!$user) {
            throw new RuntimeException('The user does not exist.');
        }

        $token = $this->jwtAuth->refresh($token);
        return $this->item(new AuthToken(['token' => $token]));
    }

    /**
     * Login with a single use token.
     *
     * @param  Request  $request
     * @param  \App\Repositories\UserRepository  $user
     * @return Response
     */
    public function token(Request $request, UserRepository $user)
    {
        $header = $request->headers->get('authorization');
        if (! starts_with(strtolower($header), 'token')) {
            throw new BadRequestException('Single use token not provided.');
        }

        $token = trim(substr($header, 5));

        // If we didn't find the user, it was an expired/invalid token. No access granted
        if (!$user = $user->findByLoginToken($token)) {
            throw new UnauthorizedException('Token invalid.');
        }

        $token = $this->jwtAuth->fromUser($user);
        return $this->item(new AuthToken(['token' => $token]));
    }
}
