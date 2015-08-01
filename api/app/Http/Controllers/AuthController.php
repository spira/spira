<?php

namespace App\Http\Controllers;

Use Log;
use RuntimeException;
use Tymon\JWTAuth\JWTAuth;
use App\Models\SocialLogin;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Contracts\Auth\Guard as Auth;
use App\Http\Transformers\AuthTokenTransformer;
use App\Exceptions\UnprocessableEntityException;
use Illuminate\Contracts\Foundation\Application;
use App\Extensions\Socialite\Parsers\ParserFactory;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spira\Responder\Contract\ApiResponderInterface as Responder;

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
     * @param  Auth         $auth
     * @param  JWTAuth      $jwtAuth
     * @param  Responder    $responder
     * @param  Application  $app
     * @return void
     */
    public function __construct(Auth $auth, JWTAuth $jwtAuth, Responder $responder, Application $app)
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
            $token = $this->jwtAuth->fromUser($this->auth->user(), ['method' => 'password']);
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
     * @param  string          $provider
     * @param  Socialite       $socialite
     * @param  UserRepository  $repository
     * @return Response
     */
    public function handleProviderCallback($provider, Socialite $socialite, UserRepository $repository)
    {
        $this->validateProvider($provider);

        $socialUser = $socialite->with($provider)->stateless()->user();

        // Verify so we received an email address, if using oAuth credentials
        // with Twitter for instance, that isn't whitelisted, no email
        // address will be returned with the response.
        // See the notes in Spira API doc under Social Login for more info.
        if (!$socialUser->email) {
            // The app is connected with the service, but the 3rd party service
            // is not configured or allowed to return email addresses, so we
            // can't process the data further. Let's throw an exception.
            Log::critical('Social provider '.$provider.' does not return emails.');
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
        $returnUrl = $socialite->with($provider)->stateless()->returnUrl();

        return $this->responder->redirect($returnUrl, 302, ['token' => $token]);
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
