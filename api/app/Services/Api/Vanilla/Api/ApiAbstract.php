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
     * @param string $path
     * @param array  $parameters
     * @param array  $headers
     *
     * @return string
     */
    protected function get($path, array $parameters = [], array $headers = [])
    {
        $response = $this->client->get($path, $parameters, $headers);

        return (string) $response->getBody();
    }

    /**
     * Send a POST request with JSON encoded parameters.
     *
     * @param string $path
     * @param array  $parameters
     * @param array  $headers
     *
     * @return string
     */
    protected function post($path, array $parameters = [], array $headers = [])
    {
        return $this->postRaw(
            $path,
            $this->createJsonBody($parameters),
            $headers
        );
    }

    /**
     * Send a POST request with raw data.
     *
     * @param string $path
     * @param mixed  $body
     * @param array  $headers
     *
     * @return string
     */
    protected function postRaw($path, $body, array $headers = [])
    {
        $response = $this->client->post(
            $path,
            $body,
            $headers
        );

        return (string) $response->getBody();
    }

    /**
     * Send a PUT request with JSON-encoded parameters.
     *
     * @param string $path
     * @param array  $parameters
     * @param array  $headers
     *
     * @return  string
     */
    protected function put($path, array $parameters = [], array $headers = [])
    {
        $response = $this->client->put(
            $path,
            $this->createJsonBody($parameters),
            $headers
        );

        return (string) $response->getBody();
    }

    /**
     * Send a DELETE request with JSON-encoded parameters.
     *
     * @param string $path
     * @param array  $parameters
     * @param array  $headers
     *
     * @return  string
     */
    protected function delete($path, array $parameters = [], array $headers = [])
    {
        $response = $this->client->delete(
            $path,
            $this->createJsonBody($parameters),
            $headers
        );

        return (string) $response->getBody();
    }

    /**
     * Create a JSON encoded version of an array of parameters.
     *
     * @param array $parameters
     *
     * @return null|string
     */
    protected function createJsonBody(array $parameters)
    {
        return (count($parameters) === 0)
            ? null
            : json_encode($parameters, empty($parameters)
                ? JSON_FORCE_OBJECT
                : 0);
    }
}
