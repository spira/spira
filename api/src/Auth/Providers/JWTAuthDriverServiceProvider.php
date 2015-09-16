<?php


namespace Spira\Auth\Providers;

use Carbon\Carbon;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Spira\Auth\Driver\Guard;
use Spira\Auth\Payload\PayloadFactory;
use Spira\Auth\Payload\PayloadValidationFactory;
use Spira\Auth\Token\JWTInterface;
use Spira\Auth\Token\NamshiAdapter;
use Spira\Auth\Token\RequestParser;
use Spira\Auth\Token\TokenExpiredException;
use Spira\Auth\Token\TokenInvalidException;
use Spira\Auth\User\UserProvider;



abstract class JWTAuthDriverServiceProvider extends ServiceProvider
{

    protected $algorithm = 'RS256';

    protected $requestMethod = 'bearer';

    protected $requestHeader = 'authorization';

    protected $requestQuery = 'token';

    protected $requestCookie = 'token';

    protected $ttl = 60;


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerUserProvider();
        $this->registerPayloadFactory();
        $this->registerPayloadValidatorFactory();
        $this->registerTokenizer();
        $this->registerRequestParser();
        $this->registerDriver();
    }

    protected function registerUserProvider()
    {
        $this->app->bind(UserProviderContract::class, function($app) {
            $model = $app['config']['auth.model'];
            return new UserProvider($app['hash'], $model, $this->getTokenUserProvider());
        });
    }

    protected function registerRequestParser()
    {
        $this->app->bind(RequestParser::class, function($app){
            return new RequestParser(
                $this->requestMethod,
                $this->requestHeader,
                $this->requestQuery,
                $this->requestCookie
            );
        });
    }

    protected function registerDriver()
    {
        $this->app->extend('auth', function(AuthManager $auth, $app){
            return $auth->extend('spira', function($app) {
                return $app[Guard::class];
            });
        });

        //extra rebinding for tests
        /** @var Application $app */
        $app = $this->app;
        $app->rebinding(Request::class, function(Application $app, Request $request){
            if ($app->resolved('auth')){
                $app['auth']->setRequest($request);
            }
        });
    }

    protected function registerTokenizer()
    {
        $this->app->bind(JWTInterface::class, function($app){
            return new NamshiAdapter($this->getSecretPublic(), $this->getSecretPrivate(), $this->algorithm);
        });
    }

    protected function registerPayloadFactory()
    {
        $this->app->bind(PayloadFactory::class, function($app){
            return new PayloadFactory($this->getPayloadGenerators());
        });
    }

    protected function registerPayloadValidatorFactory()
    {
        $this->app->bind(PayloadValidationFactory::class, function($app){
            return new PayloadValidationFactory($this->getValidationRules());
        });
    }

    protected function getTokenUserProvider()
    {
        return null;
    }


    /**
     * iis - the Issuer claim
     * iat - Issued At claim
     * exp - Expired At claim
     * nbf - Not Before claim
     * jti - token unique id
     *
     * @return array
     */
    protected function getPayloadGenerators()
    {
        /** @var Request $request */
        $request = $this->app['request'];
        return [
            'iss'=> function() use ($request) { return $request->url();},
            'iat'=> function(){ return Carbon::now()->format('U');},
            'exp'=> function(){ return Carbon::now()->addMinutes($this->ttl)->format('U');},
            'nbf'=> function(){ return Carbon::now()->format('U');},
            'jti'=> function(){ return str_random(16);},
        ];
    }

    protected function getValidationRules()
    {
        return [
            'nbf' => function($payload){
                if ( Carbon::createFromTimeStampUTC($payload['nbf'])->isFuture()) {
                    throw new TokenInvalidException('Not Before (nbf) timestamp cannot be in the future', 400);
                }

                return true;
            },
            'iat' => function($payload){
                if ( Carbon::createFromTimeStampUTC($payload['iat'])->isFuture()) {
                    throw new TokenInvalidException('Issued At (iat) timestamp cannot be in the future', 400);
                }

                return true;
            },
            'exp' => function($payload){
                if ( Carbon::createFromTimeStampUTC($payload['exp'])->isPast()) {
                    throw new TokenExpiredException('Token has expired');
                }

                return true;
            },
            'structure' => function($payload){
                if (count(array_diff_key($this->getPayloadGenerators(), array_keys($payload))) !== 0) {
                    throw new TokenInvalidException('JWT payload does not contain the required claims');
                }

                return true;
            }
        ];
    }

    abstract protected function getSecretPublic();

    abstract protected function getSecretPrivate();
}