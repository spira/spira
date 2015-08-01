<?php

namespace App\Extensions\Socialite\One;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use League\OAuth1\Client\Server\Server;
use Illuminate\Cache\CacheManager as Cache;
use Symfony\Component\HttpFoundation\RedirectResponse;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use Laravel\Socialite\One\AbstractProvider as AbstractProviderBase;
use App\Extensions\Socialite\Contracts\Provider as ProviderContract;

abstract class AbstractProvider extends AbstractProviderBase implements ProviderContract
{
    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $stateless = false;

    /**
     * Cache repository.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Create a new provider instance.
     *
     * @param  Request  $request
     * @param  Server   $server
     * @param  Cache    $cache
     *
     * @return void
     */
    public function __construct(Request $request, Server $server, Cache $cache)
    {
        $this->server = $server;
        $this->request = $request;
        $this->cache = $cache;
    }

    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return RedirectResponse
     */
    public function redirect()
    {
        $temp = $this->server->getTemporaryCredentials();

        if ($this->usesState()) {
            $this->request->getSession()->set('oauth.temp', $temp);
        } else {
            // If we have a stateless app without sessions, use the cache to
            // store the secret for man in the middle attack protection
            $key = 'oauth_temp_'.$temp->getIdentifier();
            $this->cache->put($key, $temp, ProviderContract::CACHE_TTL);
        }

        $this->storeReturnUrl($temp);

        return new RedirectResponse($this->server->getAuthorizationUrl($temp));
    }

    /**
     * Store a return url in the cache if provided.
     *
     * @param  TemporaryCredentials  $temp
     *
     * @return void
     */
    protected function storeReturnUrl(TemporaryCredentials $temp)
    {
        if ($url = $this->request->get('return_url')) {
            $key = 'oauth_return_url_'.$temp->getIdentifier();
            $this->cache->put($key, $url, ProviderContract::CACHE_TTL);
        }
    }

    /**
     * Get the return url for the request.
     *
     * @return string
     */
    public function getCachedReturnUrl()
    {
        $key = 'oauth_return_url_'.$this->request->get('oauth_token');

        // If we have no return url stored, redirect back to root page
        $url = $this->cache->get($key, Config::get('hosts.app'));

        return $url;
    }

    /**
     * Get the token credentials for the request.
     *
     * @return \League\OAuth1\Client\Credentials\TokenCredentials
     */
    protected function getToken()
    {
        if ($this->usesState()) {
            $temp = $this->request->getSession()->get('oauth.temp');
        } else {
            // If we have a stateless app without sessions, use the cache to
            // retrieve the temp credentials for man in the middle attack
            // protection
            $key = 'oauth_temp_'.$this->request->get('oauth_token');
            $temp = $this->cache->get($key, '');
        }

        return $this->server->getTokenCredentials(
            $temp,
            $this->request->get('oauth_token'),
            $this->request->get('oauth_verifier')
        );
    }

    /**
     * Determine if the provider is operating with state.
     *
     * @return bool
     */
    protected function usesState()
    {
        return ! $this->stateless;
    }

    /**
     * Indicates that the provider should operate as stateless.
     *
     * @return $this
     */
    public function stateless()
    {
        $this->stateless = true;

        return $this;
    }
}
