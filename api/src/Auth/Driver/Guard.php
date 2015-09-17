<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */


namespace Spira\Auth\Driver;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Spira\Auth\Blacklist\Blacklist;
use Spira\Auth\Payload\PayloadFactory;
use Spira\Auth\Payload\PayloadValidationFactory;
use Spira\Auth\Token\JWTInterface;
use Spira\Auth\Token\RequestParser;
use Spira\Auth\Token\TokenExpiredException;
use Spira\Contract\Exception\NotImplementedException;

class Guard implements \Illuminate\Contracts\Auth\Guard
{
    /**
     * @var UserProvider
     */
    protected $provider;

    /**
     * The currently authenticated user.
     *
     * @var Authenticatable
     */
    protected $user;

    /**
     * @var bool
     */
    protected $viaToken = false;

    /**
     * @var PayloadFactory
     */
    protected $payloadFactory;

    /**
     * @var JWTInterface
     */
    protected $tokenizer;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var PayloadValidationFactory
     */
    protected $validationFactory;
    /**
     * @var RequestParser
     */
    protected $requestParser;
    /**
     * @var Blacklist
     */
    protected $blacklist;

    /**
     * @param JWTInterface $tokenizer
     * @param PayloadFactory $payloadFactory
     * @param PayloadValidationFactory $validationFactory
     * @param UserProvider $provider
     * @param RequestParser $requestParser
     * @param Blacklist $blacklist
     */
    public function __construct(
        JWTInterface $tokenizer,
        PayloadFactory $payloadFactory,
        PayloadValidationFactory $validationFactory,
        UserProvider $provider,
        RequestParser $requestParser,
        Blacklist $blacklist
    ) {
        $this->payloadFactory = $payloadFactory;
        $this->provider = $provider;
        $this->tokenizer = $tokenizer;
        $this->validationFactory = $validationFactory;
        $this->requestParser = $requestParser;
        $this->blacklist = $blacklist;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return (bool) $this->user;
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if ($this->user === false) {
            return;
        }

        if ($this->user) {
            return $this->user;
        }

        try {
            $user = $this->getUserFromRequest();
        } catch (\Exception $e) {
            return;
        }

        if ($user) {
            $this->user = $user;
            $this->viaToken = true;
        }

        return $user;
    }

    /**
     * Get the token of currently authenticated user.
     *
     * @return string|null
     */
    public function token()
    {
        if ($this->user) {
            return $this->generateToken($this->user);
        }

        return;
    }

    /**
     * @return Authenticatable|null
     */
    public function getUserFromRequest()
    {
        $token = $this->getRequestParser()->getToken($this->getRequest());
        $payload = $this->getTokenizer()->decode($token);
        $this->blacklist->check($payload);
        $this->getValidationFactory()->validatePayload($payload);
        $user = $this->getProvider()->retrieveByToken(null, $payload);

        return $user;
    }

    /**
     * Log a user into the application without token.
     *
     * @param  array $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        return $this->attempt($credentials, false, true);
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  array $credentials
     * @param  bool $remember
     * @param  bool $login
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = true, $login = true)
    {
        $user = $this->getProvider()->retrieveByCredentials($credentials);
        if ($user  && $this->getProvider()->validateCredentials($user, $credentials)) {
            if ($login) {
                $this->login($user);
            }

            return true;
        }

        return false;
    }

    /**
     * Attempt to authenticate using HTTP Basic Auth.
     *
     * @param  string $field
     * @return null|\Symfony\Component\HttpFoundation\Response
     * @throws NotImplementedException
     */
    public function basic($field = 'email')
    {
        throw new NotImplementedException;
    }

    /**
     * Perform a stateless HTTP Basic login attempt.
     *
     * @param  string $field
     * @return null|\Symfony\Component\HttpFoundation\Response
     * @throws NotImplementedException
     */
    public function onceBasic($field = 'email')
    {
        throw new NotImplementedException;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     * @throws NotImplementedException
     */
    public function validate(array $credentials = [])
    {
        throw new NotImplementedException;
    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  bool $remember set or update token
     * @return void
     */
    public function login(Authenticatable $user, $remember = false)
    {
        $this->user = $user;
    }

    /**
     * Log the given user ID into the application.
     *
     * @param  mixed $id auth token
     * @param  bool $remember
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function loginUsingId($id, $remember = false)
    {
        $user = $this->provider->retrieveById($id);
        if ($user) {
            $this->login($user);
        }

        return $user;
    }

    /**
     * Determine if the user was authenticated via token.
     *
     * @return bool
     */
    public function viaRemember()
    {
        throw new NotImplementedException;
    }

    /**
     * Determine if the user was authenticated via token.
     *
     * @return bool
     */
    public function viaToken()
    {
        return $this->viaToken;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        if ($this->user){
            $this->blacklist->add($this->payloadFactory->createFromUser($this->user));
        }
        $this->user = false;
    }

    /**
     * @param Authenticatable $user
     * @return string
     */
    public function generateToken(Authenticatable $user)
    {
        return $this->getTokenizer()->encode($this->getPayloadFactory()->createFromUser($user));
    }

    /**
     * Get the user provider used by the guard.
     *
     * @return UserProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set the user provider used by the guard.
     *
     * @param  UserProvider  $provider
     * @return void
     */
    public function setProvider(UserProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return JWTInterface
     */
    public function getTokenizer()
    {
        return $this->tokenizer;
    }

    /**
     * @return PayloadFactory
     */
    public function getPayloadFactory()
    {
        return $this->payloadFactory;
    }

    /**
     * @return RequestParser
     */
    public function getRequestParser()
    {
        return $this->requestParser;
    }

    /**
     * Get the current request instance.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the current request instance.
     *
     * @param  Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return PayloadValidationFactory
     */
    public function getValidationFactory()
    {
        return $this->validationFactory;
    }

    /**
     * @return Blacklist
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }
}
