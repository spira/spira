<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 11.09.15
 * Time: 13:55
 */

namespace Spira\Auth\Driver;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Spira\Auth\Payload\PayloadFactory;
use Spira\Auth\Payload\PayloadValidationFactory;
use Spira\Auth\Token\JWTInterface;


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
     * @var PayloadFactory
     */
    protected $payloadFactory;
    /**
     * @var JWTInterface
     */
    protected $tokenizer;
    /**
     * @var PayloadValidationFactory
     */
    private $validationFactory;

    public function __construct(
        JWTInterface $tokenizer,
        PayloadFactory $payloadFactory,
        PayloadValidationFactory $validationFactory,
        UserProvider $provider
    )
    {

        $this->payloadFactory = $payloadFactory;
        $this->provider = $provider;
        $this->tokenizer = $tokenizer;
        $this->validationFactory = $validationFactory;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        // TODO: Implement check() method.
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        // TODO: Implement guest() method.
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if ($this->user === false){
            return null;
        }

        if ($this->user){
            return $this->user;
        }
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
        if ($user  && $this->getProvider()->validateCredentials($user, $credentials)){
            if ($login) {
                $this->login($user, $remember);
            }

            return true;
        }

        return false;
    }

    /**
     * Attempt to authenticate using HTTP Basic Auth.
     *
     * @param  string $field
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function basic($field = 'email')
    {
        // TODO: Implement basic() method.
    }

    /**
     * Perform a stateless HTTP Basic login attempt.
     *
     * @param  string $field
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function onceBasic($field = 'email')
    {
        // TODO: Implement onceBasic() method.
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        // TODO: Implement validate() method.
    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  bool $remember
     * @return void
     */
    public function login(Authenticatable $user, $remember = false)
    {
        $this->user = $user;
        $user->setRememberToken($this->generateToken($user));
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
        // TODO: Implement loginUsingId() method.
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember()
    {
        // TODO: Implement viaRemember() method.
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        // TODO: Implement logout() method.
    }

    /**
     * @param Authenticatable $user
     * @return string
     */
    protected function generateToken(Authenticatable $user)
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

}