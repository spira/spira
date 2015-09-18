<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Socialite\Two;

use Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Extensions\Socialite\Contracts\Provider as ProviderContract;

trait ProviderTrait
{
    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @return RedirectResponse
     */
    public function redirect()
    {
        $state = Str::random(40);

        $this->storeReturnUrl($state);

        return new RedirectResponse($this->getAuthUrl($state));
    }

    /**
     * Store a return url in the cache if provided.
     *
     * @param  string  $state
     *
     * @return void
     */
    protected function storeReturnUrl($state)
    {
        if ($url = $this->request->input('return_url')) {
            $key = 'oauth_return_url_'.$state;
            Cache::put($key, $url, ProviderContract::CACHE_TTL);
        }
    }

    /**
     * Get the return url for the request.
     *
     * @return string
     */
    public function getCachedReturnUrl()
    {
        $key = 'oauth_return_url_'.$this->request->input('state');

        // If we have no return url stored, redirect back to root page
        $url = Cache::get($key, Config::get('hosts.app'));

        return $url;
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string|null  $state
     *
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId, 'redirect_uri' => $this->redirectUrl,
            'scope' => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'response_type' => 'code',
            'state' => $state,
        ];

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        $user = $this->mapUserToObject($this->getUserByToken(
            $token = $this->getAccessToken($this->getCode())
        ));

        return $user->setToken($token);
    }
}
