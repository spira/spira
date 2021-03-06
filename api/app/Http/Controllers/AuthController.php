<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Extensions\Socialite\SocialiteManager;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Spira\Auth\Driver\Guard as SpiraGuard;
use Spira\Auth\Token\TokenInvalidException;
use Spira\Auth\Token\TokenIsMissingException;
use App\Models\SocialLogin;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard as Auth;
use App\Http\Transformers\AuthTokenTransformer;
use Illuminate\Contracts\Foundation\Application;
use App\Services\SingleSignOn\SingleSignOnFactory;
use App\Extensions\Socialite\Parsers\ParserFactory;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spira\Core\Contract\Exception\BadRequestException;
use Spira\Core\Contract\Exception\NotImplementedException;
use Spira\Core\Contract\Exception\UnauthorizedException;
use Spira\Core\Contract\Exception\UnprocessableEntityException;
use Spira\Core\Controllers\ApiController;
use Spira\Core\Responder\Response\ApiResponse;

class AuthController extends ApiController
{
    /**
     * @var SpiraGuard
     */
    protected $auth;

    /**
     * Enable permissions checks.
     */
    protected $permissionsEnabled = true;
    protected $defaultRole = false;

    /**
     * Assign dependencies.
     *
     * @param Auth $auth
     * @param AuthTokenTransformer $transformer
     * @param Application $app
     */
    public function __construct(Guard $auth, AuthTokenTransformer $transformer)
    {
        $this->auth = $auth;

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

        if (! $this->auth->attempt($credentials)) {
            if ($oldEmail = User::findCurrentEmail($credentials['email'])) {
                $credentials['email'] = $oldEmail;
                if (! $this->auth->attempt($credentials)) {
                    throw new UnauthorizedException('Credentials failed.');
                }
            } else {
                throw new UnauthorizedException('Credentials failed.');
            }
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($this->auth->token());
    }

    /**
     * Refresh a login token.
     *
     * @return ApiResponse
     */
    public function refresh()
    {
        $user = $this->auth->getUserFromRequest();

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($this->auth->generateToken($user));
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
            throw new TokenIsMissingException('Single use token not provided.');
        }

        $token = trim(substr($header, 5));

        // If we didn't find the user, it was an expired/invalid token. No access granted
        if (! $user = $userModel->findByLoginToken($token)) {
            throw new TokenInvalidException('Invalid single use token.');
        }

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($this->auth->generateToken($user));
    }

    public function loginAsUser(Request $request, User $userModel, $userId)
    {

        /** @var User $user */
        $user = $userModel->findByIdentifier($userId);

        $this->checkPermission(static::class.'@loginAsUser', ['model' => $user]);

        $user->setCurrentAuthMethod('impersonation');

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($this->auth->generateToken($user));
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
            $user->fill(array_merge($socialUser->toArray()));
            $user->save();
        }

        $socialLogin = new SocialLogin(['provider' => $provider, 'token' => $socialUser->token]);
        /* @var User $user */
        $user->addSocialLogin($socialLogin);

        // Prepare response data
        $user->setCurrentAuthMethod($provider);
        $returnUrl = $socialite->with($provider)->getCachedReturnUrl().'?jwtAuthToken='.$this->auth->generateToken($user);

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
     * @return ApiResponse
     */
    public function singleSignOn($requester, Request $request)
    {
        $user = $request->user();
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
        if (! in_array($provider, array_keys(app()['config']['services']))) {
            throw new NotImplementedException('Provider '.$provider.' is not supported.');
        }
    }
}
