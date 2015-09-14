<?php


namespace Spira\Auth\Providers;

use Carbon\Carbon;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Spira\Auth\Driver\Guard;
use Spira\Auth\Payload\PayloadFactory;
use Spira\Auth\Payload\PayloadValidationFactory;
use Spira\Auth\Token\JWTInterface;
use Spira\Auth\Token\NamshiAdapter;
use Spira\Auth\User\UserProvider;


abstract class JWTAuthDriverServiceProvider extends ServiceProvider
{

    protected $algorithm = 'RS256';

    protected $ttl = 60;


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerLoginUserProvider();
        $this->registerPayloadFactory();
        $this->registerPayloadValidatorFactory();
        $this->registerTokenizer();
        $this->registerDriver();
    }

    protected function registerLoginUserProvider()
    {
        $this->app->bind(UserProviderContract::class, function($app) {
            $model = $app['config']['auth.model'];
            return new UserProvider($app['hash'], $model, $this->getTokenUserProvider());
        });
    }

    protected function registerDriver()
    {
        $this->app->make('auth')->extend('spira', function($app) {
            return $app[Guard::class];
        });
    }

    protected function registerTokenizer()
    {
        $this->app->singleton(JWTInterface::class, function($app){
            return new NamshiAdapter($this->getSecretPublic(), $this->getSecretPrivate(), $this->algorithm);
        });
    }

    protected function registerPayloadFactory()
    {
        $this->app->singleton(PayloadFactory::class, function($app){
            return new PayloadFactory($this->getPayloadGenerators());
        });
    }

    protected function registerPayloadValidatorFactory()
    {
        $this->app->singleton(PayloadValidationFactory::class, function($app){
            return new PayloadValidationFactory($this->getValidationRules());
        });
    }

    protected function getTokenUserProvider()
    {
        return null;
    }


    /**
     * sub - user id
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
            'sub' => function(Authenticatable $user){return $user->getAuthIdentifier();},
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

        ];
    }

    abstract protected function getSecretPublic();

    abstract protected function getSecretPrivate();
}