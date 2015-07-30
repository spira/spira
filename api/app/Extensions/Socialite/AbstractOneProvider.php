<?php

namespace App\Extensions\Socialite;

use Illuminate\Http\Request;
use League\OAuth1\Client\Server\Server;
use Laravel\Socialite\One\AbstractProvider;
use Illuminate\Cache\CacheManager as Cache;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractOneProvider extends AbstractProvider
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
            $this->cache->put($key, $temp, 30);
        }

        return new RedirectResponse($this->server->getAuthorizationUrl($temp));
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
