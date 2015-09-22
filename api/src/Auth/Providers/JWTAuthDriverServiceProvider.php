<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Auth\Providers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Spira\Auth\Driver\Guard;
use Laravel\Lumen\Application;
use Illuminate\Auth\AuthManager;
use Spira\Auth\User\UserProvider;
use Spira\Auth\Token\JWTInterface;
use Spira\Auth\Token\NamshiAdapter;
use Spira\Auth\Blacklist\Blacklist;
use Spira\Auth\Token\RequestParser;
use Spira\Auth\Blacklist\CacheDriver;
use Spira\Auth\Payload\PayloadFactory;
use Illuminate\Support\ServiceProvider;
use Spira\Auth\Blacklist\StorageInterface;
use Spira\Auth\Token\TokenExpiredException;
use Spira\Auth\Token\TokenInvalidException;
use Spira\Auth\Payload\PayloadValidationFactory;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;

abstract class JWTAuthDriverServiceProvider extends ServiceProvider
{
    /**
     * @var string token encode/decode algorithm
     */
    protected $algorithm = 'RS256';

    /**
     * @var string
     * @see registerRequestParser
     */
    protected $requestMethod = 'bearer';

    /**
     * @var string
     * @see registerRequestParser
     */
    protected $requestHeader = 'authorization';

    /**
     * @var string
     * @see registerRequestParser
     */
    protected $requestQuery = 'token';

    /**
     * @var string
     * @see registerRequestParser
     */
    protected $requestCookie = 'token';

    /**
     * @var int expiration of token in minutes
     */
    protected $ttl = 60;

    /**
     * @var string token unique id key name inside payload
     * needed for blacklist
     */
    protected $tokenKey = 'jti';

    /**
     * @var string token expired key name inside payload
     * needed for blacklist
     */
    protected $tokenExp = 'exp';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->registerConfig();

        $this->registerUserProvider();
        $this->registerPayloadFactory();
        $this->registerPayloadValidatorFactory();
        $this->registerTokenizer();
        $this->registerRequestParser();
        $this->registerBlackList();
        $this->registerBlackListDriver();
        $this->registerDriver();
    }

    /**
     * Register the configuration file, merging with local config
     */
    protected function registerConfig()
    {
        $configPath = __DIR__ . '/../config/jwt.php';
        $this->mergeConfigFrom($configPath, 'jwt');

        $this->requestCookie = $this->app->config->get('jwt.token_keys.cookie', $this->requestCookie);
        $this->requestQuery = $this->app->config->get('jwt.token_keys.query', $this->requestQuery);
    }

    /**
     * Bind our custom user provider
     * This is a base method you don't want to override.
     *
     * @see getTokenUserProvider
     */
    protected function registerUserProvider()
    {
        $this->app->bind(UserProviderContract::class, function ($app) {
            $model = $app['config']['auth.model'];

            return new UserProvider($app['hash'], $model, $this->getTokenUserProvider());
        });

        // due to lumen custom request alias rebinding
        // we are forced to duplicate user resolver callback to the request
        $this->app->rebinding(Request::class, function ($app, $request) {
            $request->setUserResolver(function () use ($app) {
                return $app['auth']->user();
            });
        });
    }

    /**
     * Parses request for the token data
     * override.
     *
     *  $this->requestMethod,
     *  $this->requestHeader,
     *  $this->requestQuery,
     *  $this->requestCookie
     *
     * to set custom headers
     */
    protected function registerRequestParser()
    {
        $this->app->bind(RequestParser::class, function ($app) {
            return new RequestParser(
                $this->requestMethod,
                $this->requestHeader,
                $this->requestQuery,
                $this->requestCookie
            );
        });
    }

    /**
     * Base token auth driver registration.
     */
    protected function registerDriver()
    {
        $this->app->extend('auth', function (AuthManager $auth, $app) {
            return $auth->extend('jwt', function ($app) {
                return $app[Guard::class];
            });
        });

        //extra rebinding for tests
        /** @var Application $app */
        $app = $this->app;
        $app->rebinding(Request::class, function (Application $app, Request $request) {
            if ($app->resolved('auth')) {
                $app['auth']->setRequest($request);
            }
        });
    }

    /**
     * Tokenizer adapter.
     */
    protected function registerTokenizer()
    {
        $this->app->bind(JWTInterface::class, function ($app) {
            return new NamshiAdapter($this->getSecretPublic(), $this->getSecretPrivate(), $this->algorithm);
        });
    }

    /**
     * Base payload factory
     * Rules can be overridden in.
     * @see getPayloadGenerators
     */
    protected function registerPayloadFactory()
    {
        $this->app->bind(PayloadFactory::class, function ($app) {
            return new PayloadFactory($this->getPayloadGenerators());
        });
    }

    /**
     * Base payload validator factory
     * Rules can be overridden in.
     * @see getValidationRules
     */
    protected function registerPayloadValidatorFactory()
    {
        $this->app->bind(PayloadValidationFactory::class, function ($app) {
            return new PayloadValidationFactory($this->getValidationRules());
        });
    }

    /**
     * Custom function to get user from token
     * Example of usage.
     *
     * /**
     *  *Get token user provider closure
     *  * @return \Closure
     *  *
     *   return function($payload, UserProvider $provider){
     *       if (isset($payload['_user']) && $payload['_user']){
     *           $userData = $payload['_user'];
     *           $user = $provider->createModel();
     *           foreach($userData as $key => $value){
     *               if (is_string($value)){
     *                   $user->{$key} = $value;
     *               }
     *           }
     *
     *           return $user;
     *       }
     *
     *       if (isset($payload['sub']) && $payload['sub']){
     *           return $provider->retrieveById($payload['sub']);
     *       }
     *
     *       return null;
     *   };
     *
     * @return null
     */

    /**
     * Get token user provider closure.
     * @return \Closure
     */
    abstract protected function getTokenUserProvider();

    /**
     * iis - the Issuer claim
     * iat - Issued At claim
     * exp - Expired At claim
     * nbf - Not Before claim
     * jti - token unique id.
     *
     * @return array
     */
    protected function getPayloadGenerators()
    {
        /** @var Request $request */
        $request = $this->app['request'];

        return [
            'iss' => function () use ($request) { return $request->url();},
            'iat' => function () { return Carbon::now()->format('U');},
            'exp' => function () { return Carbon::now()->addMinutes($this->ttl)->format('U');},
            'nbf' => function () { return Carbon::now()->format('U');},
            'jti' => function () { return str_random(16);},
        ];
    }

    /**
     * Validation rules for payload.
     * @return array
     */
    protected function getValidationRules()
    {
        return [
            'nbf' => function ($payload) {
                if (Carbon::createFromTimeStampUTC($payload['nbf'])->isFuture()) {
                    throw new TokenInvalidException('Not Before (nbf) timestamp cannot be in the future');
                }

                return true;
            },
            'iat' => function ($payload) {
                if (Carbon::createFromTimeStampUTC($payload['iat'])->isFuture()) {
                    throw new TokenInvalidException('Issued At (iat) timestamp cannot be in the future');
                }

                return true;
            },
            'exp' => function ($payload) {
                if (Carbon::createFromTimeStampUTC($payload['exp'])->isPast()) {
                    throw new TokenExpiredException('Token has expired');
                }

                return true;
            },
            'structure' => function ($payload) {
                if (count(array_diff_key($this->getPayloadGenerators(), array_keys($payload))) !== 0) {
                    throw new TokenInvalidException('JWT payload does not contain the required claims');
                }

                return true;
            },
        ];
    }

    /**
     * @return mixed
     */
    abstract protected function getSecretPublic();

    /**
     * @return mixed
     */
    abstract protected function getSecretPrivate();

    /**
     * Needed for token invalidation during logout
     * Optional.
     */
    protected function registerBlackList()
    {
        $this->app->bind(Blacklist::class, function ($app) {
            return new Blacklist($this->app[StorageInterface::class], $this->tokenKey, $this->tokenExp);
        });
    }

    /**
     * Driver used to store invalid tokens.
     */
    protected function registerBlackListDriver()
    {
        $this->app->bind(StorageInterface::class, function ($app) {
            return $this->app[CacheDriver::class];
        });
    }
}
