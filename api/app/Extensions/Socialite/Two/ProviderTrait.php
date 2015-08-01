<?php

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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        $state = Str::random(40);

        if ($this->usesState()) {
            $this->request->getSession()->set('state', $state);
        }

        $this->storeReturnUrl($state);

        return new RedirectResponse($this->getAuthUrl($state));
    }

    /**
     * Store a return url in the cache if provided.
     *
     * @param  string  $state
     * @return void
     */
    protected function storeReturnUrl($state)
    {
        if ($url = $this->request->get('return_url')) {
            $key = 'oauth_return_url_'.$state;
            Cache::put($key, $url, ProviderContract::CACHE_TTL);
        }
    }

    /**
     * Get the return url for the request.
     *
     * @return string
     */
    public function returnUrl()
    {
        $key = 'oauth_return_url_'.$this->request->get('state');

        // If we have no return url stored, redirect back to root page
        $url = Cache::get($key, Config::get('hosts.app'));

        return $url;
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string|null  $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId, 'redirect_uri' => $this->redirectUrl,
            'scope' => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'response_type' => 'code',
            'state' => $state
        ];

        return $fields;
    }
}
