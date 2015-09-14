<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 14.09.15
 * Time: 14:35
 */

namespace App\Providers;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Spira\Auth\Providers\JWTAuthDriverServiceProvider;
use Spira\Auth\User\SocialiteAuthenticatable;

class AuthDriverServiceProvider extends JWTAuthDriverServiceProvider
{
    protected function getPayloadGenerators()
    {
        /** @var Request $request */
        $request = $this->app['request'];
        return array_merge(parent::getPayloadGenerators(),[
            '_user' => function(Authenticatable $user){ return $user;},
            'method' => function(SocialiteAuthenticatable $user){ return $user->getCurrentAuthMethod();},
            'iss'=> function() use ($request) { return $request->getHttpHost();},
            'aud'=> function() use ($request) { return str_replace('api.', '', $request->getHttpHost());},
        ]);
    }

    protected function getTokenUserProvider()
    {
        return function($payload){
            if (isset($payload['_user'])){
                $userData = $payload['_user'];
                $user = $this->app->make(Authenticatable::class);
                foreach($userData as $key => $value){
                    $user->{$piece} = $value;
                }

                return $user;
            }

            return null;
        };
    }

    protected function getSecretPublic()
    {
        return 'file://'.storage_path('app/keys/public.pem');
    }

    protected function getSecretPrivate()
    {
        return 'file://'.storage_path('app/keys/private.pem');
    }
}