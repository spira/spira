<?php

namespace App\Http\Controllers;

use RuntimeException;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Contracts\Auth\Guard as Auth;
use App\Http\Transformers\AuthTokenTransformer;
use Illuminate\Contracts\Foundation\Application;
use Spira\Responder\Contract\ApiResponderInterface;
use Laravel\Socialite\Contracts\Factory as Socialite;

class AuthController extends ApiController
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

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
     * @param  ApiResponderInterface  $responder
     * @param  Application            $app
     * @return void
     */
    public function __construct(Auth $auth, JWTAuth $jwtAuth, ApiResponderInterface $responder, Application $app)
    {
        $this->app = $app;
        $this->auth = $auth;
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

        if (!$this->auth->attempt($credentials)) {
            throw new UnauthorizedException('Credentials failed.');
        }

        try {
            $token = $this->jwtAuth->fromUser($this->auth->user());
        } catch (JWTException $e) {
            throw new RuntimeException($e->getMessage(), 500, $e);
        }

        return $this->responder->setTransformer($this->app->make(AuthTokenTransformer::class))->item($token);
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

        return $this->responder->setTransformer($this->app->make(AuthTokenTransformer::class))->item($token);
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

        return $this->responder->setTransformer($this->app->make(AuthTokenTransformer::class))->item($token);
    }

    /**
     * Redirect the user to the Provider authentication page.
     *
     * @param  string     $provider
     * @param  Socialite  $socialite
     * @return Response
     */
    public function redirectToProvider($provider, Socialite $socialite)
    {
        $this->validateProvider($provider);

        return $socialite->with($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from Provider.
     *
     * @param  string     $provider
     * @param  Socialite  $socialite
     * @return Response
     */
    public function handleProviderCallback($provider, Socialite $socialite)
    {
        $this->validateProvider($provider);

        $user = $socialite->with($provider)->stateless()->user();

        // @todo add code to save the social login data
        var_dump($user);

        // $user->token;
    }

    /**
     * Check so the provider exists.
     *
     * @param  string  $provider
     * @return void
     */
    protected function validateProvider($provider)
    {
        if (!in_array($provider, array_keys($this->app['config']['services']))) {
            // Throws a NotFoundHttpException
            $this->app->abort(404, 'Invalid provider');
        }
    }
}
