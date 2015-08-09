<?php

namespace App\Http\Controllers;

use Log;
use RuntimeException;
use Spira\Responder\Response\ApiResponse;
use Tymon\JWTAuth\JWTAuth;
use App\Models\SocialLogin;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Exceptions\NotImplementedException;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Contracts\Auth\Guard as Auth;
use App\Http\Transformers\AuthTokenTransformer;
use App\Exceptions\UnprocessableEntityException;
use Illuminate\Contracts\Foundation\Application;
use App\Services\SingleSignOn\SingleSignOnFactory;
use App\Extensions\Socialite\Parsers\ParserFactory;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthController extends EntityController
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
     * @param  Auth                  $auth
     * @param  JWTAuth               $jwtAuth
     * @param  AuthTokenTransformer  $transformer
     * @param  Application           $app
     *
     */
    public function __construct(Auth $auth, JWTAuth $jwtAuth, AuthTokenTransformer $transformer, Application $app)
    {
        $this->auth = $auth;
        $this->jwtAuth = $jwtAuth;
        $this->transformer = $transformer;
        $this->app = $app;
    }

    /**
     * Get a login token.
     *
     * @param Request $request
     *
     * @return ApiResponse
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
            $token = $this->jwtAuth->fromUser($this->auth->user(), ['method' => 'password']);
        } catch (JWTException $e) {
            throw new RuntimeException($e->getMessage(), 500, $e);
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($token)
            ->withCookie($this->tokenToCookie($token, $request));
    }

    /**
     * Refresh a login token.
     *
     * @param Request $request
     *
     * @return ApiResponse
     */
    public function refresh(Request $request)
    {
        $token = $this->jwtAuth->getTokenFromRequest();

        // Get the user to make sure the token is fully valid
        $this->jwtAuth->getUser();

        $token = $this->jwtAuth->refresh($token);

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($token)
            ->withCookie($this->tokenToCookie($token, $request));
    }

    /**
     * Login with a single use token.
     *
     * @param  Request         $request
     * @param  UserRepository  $userRepository
     *
     * @throws BadRequestException
     * @throws UnauthorizedException
     *
     * @return ApiResponse
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

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($token)
            ->withCookie($this->tokenToCookie($token, $request));
    }

    /**
     * Redirect the user to the Provider authentication page.
     *
     * @param  string     $provider
     * @param  Socialite  $socialite
     *
     * @return ApiResponse
     */
    public function redirectToProvider($provider, Socialite $socialite)
    {
        $this->validateProvider($provider);

        return $socialite->with($provider)->redirect();
    }

    /**
     * Obtain the user information from Provider.
     *
     * @param  string          $provider
     * @param  Socialite       $socialite
     * @param  UserRepository  $repository
     *
     * @throws UnprocessableEntityException
     *
     * @return ApiResponse
     */
    public function handleProviderCallback($provider, Socialite $socialite, UserRepository $repository)
    {
        $this->validateProvider($provider);

        $socialUser = $socialite->with($provider)->user();

        // Verify so we received an email address, if using oAuth credentials
        // with Twitter for instance, that isn't whitelisted, no email
        // address will be returned with the response.
        // See the notes in Spira API doc under Social Login for more info.
        if (!$socialUser->email) {
            // The app is connected with the service, but the 3rd party service
            // is not configured or allowed to return email addresses, so we
            // can't process the data further. Let's throw an exception.
            Log::critical('Provider '.$provider.' does not return email.');
            throw new UnprocessableEntityException('User object has no email');
        }

        // Parse the social user to fit within Spira's user model
        $socialUser = ParserFactory::parse($socialUser, $provider);

        // Get or create the Spira user from the social login
        try {
            $user = $repository->findByEmail($socialUser->email);
        } catch (ModelNotFoundException $e) {
            $user = $repository->getNewModel();
            $user->fill(array_merge($socialUser->toArray(), ['user_type' => 'guest']));
            $user = $repository->save($user);
        }

        $socialLogin = new SocialLogin(['provider' => $provider, 'token' => $socialUser->token]);
        $user->addSocialLogin($socialLogin);

        // Prepare response data
        $token = $this->jwtAuth->fromUser($user, ['method' => $provider]);
        $returnUrl = $socialite->with($provider)->getCachedReturnUrl() . '?jwtAuthToken=' . $token;

        $response = $this->getResponse();
        $response->redirect($returnUrl, 302);
        return $response;
    }

    /**
     * Provide a requester with user information for single sign on.
     *
     * @param  string  $requester
     * @param  Request $request
     *
     * @return Response
     */
    public function singleSignOn($requester, Request $request)
    {
        if ($token = $request->cookie('JwtAuthToken')) {
            $user = $this->jwtAuth->toUser($token);
        } else {
            $user = null;
        }

        $requester = SingleSignOnFactory::create($requester, $request, $user);

        return $requester->getResponse();
    }


    /**
     * Prepare the token to be passed as cookie.
     *
     * @param  string  $token
     * @param  Request $request
     *
     * @return Cookie
     */
    protected function tokenToCookie($token, $request)
    {
        // Get the domain
        $segments = explode('.', $request->getHost());
        if (count($segments) > 1) {
            $segments = array_slice($segments, -2);
        }
        $domain = implode('.', $segments);

        // Set expiration to browser session
        $expiration = 0;

        return new Cookie('JwtAuthToken', $token, $expiration, '/', $domain, false, false);
    }

    /**
     * Check so the social provider exists.
     *
     * @param  string  $provider
     *
     * @throws NotImplementedException
     *
     * @return void
     */
    protected function validateProvider($provider)
    {
        if (!in_array($provider, array_keys($this->app['config']['services']))) {
            throw new NotImplementedException('Provider '.$provider.' is not supported.');
        }
    }
}
