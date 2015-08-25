<?php

namespace App\Services\Api\Vanilla\Api;

use Guzzle\Http\Message\Response;
use App\Services\Api\Vanilla\Client;

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
     * @param  Client $client
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
     * @param  string $path
     * @param  array  $parameters
     * @param  array  $headers
     *
     * @return array
     */
    protected function get($path, array $parameters = [], array $headers = [])
    {
        $response = $this->client->get($path, $parameters, $headers);

        return $this->getContent($response);
    }

    /**
     * Send a POST request with JSON encoded parameters.
     *
     * @param  string $path
     * @param  array  $parameters
     * @param  array  $headers
     *
     * @return array
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
     * @param  string $path
     * @param  mixed  $body
     * @param  array  $headers
     *
     * @return array
     */
    protected function postRaw($path, $body, array $headers = [])
    {
        $response = $this->client->post(
            $path,
            $body,
            $headers
        );

        return $this->getContent($response);
    }

    /**
     * Send a PUT request with JSON-encoded parameters.
     *
     * @param  string $path
     * @param  array  $parameters
     * @param  array  $headers
     *
     * @return array
     */
    protected function put($path, array $parameters = [], array $headers = [])
    {
        $response = $this->client->put(
            $path,
            $this->createJsonBody($parameters),
            $headers
        );

        return $this->getContent($response);
    }

    /**
     * Send a DELETE request with JSON-encoded parameters.
     *
     * @param  string $path
     * @param  array  $parameters
     * @param  array  $headers
     *
     * @return array|mixed
     */
    protected function delete($path, array $parameters = [], array $headers = [])
    {
        $response = $this->client->delete(
            $path,
            $this->createJsonBody($parameters),
            $headers
        );

        return $this->getContent($response);
    }

    /**
     * Create a JSON encoded version of an array of parameters.
     *
     * @param  array $parameters
     *
     * @return null|string
     */
    protected function createJsonBody(array $parameters)
    {
        if (count($parameters) === 0) {
            return null;
        }

        return json_encode($parameters);
    }

    /**
     * Extracts the content from the response.
     *
     * @param  Response $response
     *
     * @return array|mixed
     */
    protected function getContent(Response $response)
    {
        $body = $response->getBody(true);
        $content = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return $body;
        }

        return $content;
    }
}
