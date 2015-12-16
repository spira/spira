<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Providers;

use App\Http\Transformers\UserTokenTransformer;
use Illuminate\Http\Request;
use Spira\Auth\User\UserProvider;
use Spira\Auth\User\SocialiteAuthenticatable;
use Illuminate\Contracts\Auth\Authenticatable;
use Spira\Auth\Providers\JWTAuthDriverServiceProvider;

class AuthDriverServiceProvider extends JWTAuthDriverServiceProvider
{
    /**
     * Get the generators for the payload.
     * @return array
     */
    protected function getPayloadGenerators()
    {
        /** @var Request $request */
        $request = $this->app[Request::class];

        return array_merge(
            parent::getPayloadGenerators(),
            [
                '_user' => function (Authenticatable $user) {
                    /** @var UserTokenTransformer $transformer */
                    $transformer = $this->app->make(UserTokenTransformer::class);

                    return $transformer->transformItem($user);
                },
                'method' => function (SocialiteAuthenticatable $user) { return $user->getCurrentAuthMethod() ?: 'password'; },
                'iss' => function () use ($request) { return $request->getHttpHost(); },
                'aud' => function () use ($request) { return str_replace('api.', '', $request->getHttpHost()); },
                'sub' => function (Authenticatable $user) {return $user->getAuthIdentifier(); },
            ]
        );
    }

    /**
     * Get token user provider closure.
     * @return \Closure
     */
    protected function getTokenUserProvider()
    {
        return function ($payload, UserProvider $provider) {
            if (isset($payload['_user']) && $payload['_user']) {
                $userData = $payload['_user'];
                $user = $provider->createModel();
                foreach ($userData as $key => $value) {
                    if (is_string($value)) {
                        $user->{snake_case($key)} = $value;
                    }
                }

                return $user;
            }

            if (isset($payload['sub']) && $payload['sub']) {
                return $provider->retrieveById($payload['sub']);
            }

            return;
        };
    }

    /**
     * Get the path to the public key.
     * @return string
     */
    protected function getSecretPublic()
    {
        return 'file://'.storage_path('app/keys/public.pem');
    }

    /**
     * Get path to the private key.
     * @return string
     */
    protected function getSecretPrivate()
    {
        return 'file://'.storage_path('app/keys/private.pem');
    }
}
