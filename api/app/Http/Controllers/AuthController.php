<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Extensions\JWTAuth\JWTManager;
use App\Extensions\Socialite\SocialiteManager;
use App\Models\User;
use RuntimeException;
use Spira\Responder\Response\ApiResponse;
use Tymon\JWTAuth\JWTAuth;
use App\Models\SocialLogin;
use Illuminate\Http\Request;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Exceptions\NotImplementedException;
use Illuminate\Contracts\Auth\Guard as Auth;
use App\Http\Transformers\AuthTokenTransformer;
use App\Exceptions\UnprocessableEntityException;
use Illuminate\Contracts\Foundation\Application;
use App\Services\SingleSignOn\SingleSignOnFactory;
use App\Extensions\Socialite\Parsers\ParserFactory;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthController extends ApiController
{
    const JWT_AUTH_TOKEN_COOKIE = 'ngJwtAuthToken';
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * JWT Auth Package.
     *
     * @var \App\Extensions\JWTAuth\JWTAuth|JWTManager
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
     * @param Auth $auth
     * @param JWTAuth $jwtAuth
     * @param AuthTokenTransformer $transformer
     * @param Application $app
     * @param Cache $cache
     */
    public function __construct(
        Auth $auth,
        JWTAuth $jwtAuth,
        AuthTokenTransformer $transformer,
        Application $app)
    {
        $this->auth = $auth;
        $this->jwtAuth = $jwtAuth;
        $this->app = $app;
        parent::__construct($transformer);
    }

    /**
     * Log in a user.
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

        if (! $token = $this->attemptLogin($credentials)) {
            // Check to see if the user has recently requested to change their email and try to log in using it
            if ($oldEmail = User::findCurrentEmail($credentials['email'])) {
                $credentials['email'] = $oldEmail;
                if (! $token = $this->attemptLogin($credentials)) {
                    throw new UnauthorizedException('Credentials failed.');
                }
            } else {
                throw new UnauthorizedException('Credentials failed.');
            }
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($token);
    }

    /**
     * Attempt to login and get token.
     *
     * @param $credentials
     * @return bool|string
     */
    private function attemptLogin($credentials)
    {
        if (! $this->auth->attempt($credentials)) {
            return false;
        }

        try {
            $token = $this->jwtAuth->fromUser($this->auth->user(), ['method' => 'password']);
        } catch (JWTException $e) {
            throw new RuntimeException($e->getMessage(), 500, $e);
        }

        return $token;
    }

    /**
     * Refresh a login token.
     *
     * @return ApiResponse
     */
    public function refresh()
    {
        $token = $this->jwtAuth->getTokenFromRequest();

        // Get the user to make sure the token is fully valid
        $this->jwtAuth->getUser();

        $token = $this->jwtAuth->refresh($token);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($token);
    }

    /**
     * Login with a single use token.
     *
     * @param  Request         $request
     * @param  User  $userModel
     *
     * @throws BadRequestException
     * @throws UnauthorizedException
     *
     * @return ApiResponse
     */
    public function token(Request $request, User $userModel)
    {
        $header = $request->headers->get('authorization');
        if (! starts_with(strtolower($header), 'token')) {
            throw new BadRequestException('Single use token not provided.');
        }

        $token = trim(substr($header, 5));

        // If we didn't find the user, it was an expired/invalid token. No access granted
        if (! $user = $userModel->findByLoginToken($token)) {
            throw new UnauthorizedException('Token invalid.');
        }

        $token = $this->jwtAuth->fromUser($user);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($token);
    }

    /**
     * Redirect the user to the Provider authentication page.
     *
     * @param  string     $provider
     * @param  Socialite|SocialiteManager  $socialite
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
     * @param  Socialite|SocialiteManager       $socialite
     * @param  User  $userModel
     *
     * @throws UnprocessableEntityException
     *
     * @return ApiResponse
     */
    public function handleProviderCallback($provider, Socialite $socialite, User $userModel)
    {
        $this->validateProvider($provider);

        $socialUser = $socialite->with($provider)->user();

        // Verify so we received an email address, if using oAuth credentials
        // with Twitter for instance, that isn't whitelisted, no email
        // address will be returned with the response.
        // See the notes in Spira API doc under Social Login for more info.
        if (! $socialUser->email) {
            // The app is connected with the service, but the 3rd party service
            // is not configured or allowed to return email addresses, so we
            // can't process the data further. Let's throw an exception.
            \Log::critical('Provider '.$provider.' does not return email.');
            throw new UnprocessableEntityException('User object has no email');
        }

        // Parse the social user to fit within Spira's user model
        $socialUser = ParserFactory::parse($socialUser, $provider);

        // Get or create the Spira user from the social login
        try {
            $user = $userModel->findByEmail($socialUser->email);
        } catch (ModelNotFoundException $e) {
            $user = $userModel->newInstance();
            $user->fill(array_merge($socialUser->toArray(), ['user_type' => 'guest']));
            $user->save();
        }

        $socialLogin = new SocialLogin(['provider' => $provider, 'token' => $socialUser->token]);
        $user->addSocialLogin($socialLogin);

        // Prepare response data
        $token = $this->jwtAuth->fromUser($user, ['method' => $provider]);
        $returnUrl = $socialite->with($provider)->getCachedReturnUrl().'?jwtAuthToken='.$token;

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
        // A single sign on request might have different requirements and
        // methods how to deal with a non logged in user. So we get the user
        // if possible, and if not we pass in a null user and let the the
        // requester class deal with it according to the requester's definitions
        if ($token = $request->cookie(self::JWT_AUTH_TOKEN_COOKIE)) {
            $user = $this->jwtAuth->toUser($token);
        } else {
            $user = null;
        }

        $requester = SingleSignOnFactory::create($requester, $request, $user);

        return $requester->getResponse();
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
        if (! in_array($provider, array_keys($this->app['config']['services']))) {
            throw new NotImplementedException('Provider '.$provider.' is not supported.');
        }
    }
}
