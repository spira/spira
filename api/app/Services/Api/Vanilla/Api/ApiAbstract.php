<?php

namespace App\Services\Api\Vanilla\Api;

use App\Services\Api\Vanilla\Client;
use Github\HttpClient\Message\ResponseMediator;

abstract class ApiAbstract implements ApiInterface
{
    /**
     * The client.
     *
     * @var Client
     */
    protected $client;

    /**
     * Assign dependencies.
     *
     * @param Client $client
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send a GET request with query parameters.
     *
     * @param string $path           Request path.
     * @param array  $parameters     GET parameters.
     * @param array  $requestHeaders Request Headers.
     *
     * @return string
     */
    protected function get($path, array $parameters = array(), $requestHeaders = array())
    {
        $response = $this->client->get($path, $parameters, $requestHeaders);

        return (string) $response->getBody();
    }
}
