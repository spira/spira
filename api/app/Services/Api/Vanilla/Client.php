<?php

namespace App\Services\Api\Vanilla;

use InvalidArgumentException;
use Guzzle\Http\Client as GuzzleClient;

class Client
{
    /**
     * API base URL.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * API secret key.
     *
     * @var string
     */
    protected $secret;

    /**
     * Default request headers.
     *
     * @var array
     */
    protected $headers = ['content-type' => 'application/json'];

    /**
     * The Guzzle instance used to communicate with the API.
     *
     * @var GuzzleClient
     */
    protected $client;

    /**
     * Map group name to class names.
     *
     * @var array
     */
    protected $map = [
        'configuration' => 'Configuration',
        'discussions' => 'Discussion',
    ];

    /**
     * Assign dependencies.
     *
     * @param GuzzleClient $client
     *
     * @return void
     */
    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;

        $this->secret = getenv('VANILLA_API_SECRET');
        $this->baseUrl = getenv('FORUM_HOST').'/api/';

        $this->client->setBaseUrl($this->baseUrl);
    }

    /**
     * Retrieve the API group to call a method within.
     *
     * @param string $group
     *
     * @throws InvalidArgumentException
     *
     * @return ApiInterface
     */
    public function api($group)
    {
        if (array_key_exists($group, $this->map)) {
            $apiClass = sprintf('%s\\Api\\%s', __NAMESPACE__, $this->map[$group]);

            $api = new $apiClass($this);
        } else {
            throw new InvalidArgumentException(
                sprintf('Undefined API group called: "%s"', $group)
            );
        }

        return $api;
    }

    /**
     * Make a GET request.
     *
     * @param string  $path
     * @param array   $parameters
     * @param array   $headers
     *
     * @return \Guzzle\Http\Message\Request
     */
    public function get($path, array $parameters = [], array $headers = [])
    {
        return $this->request($path, null, 'GET', $headers, ['query' => $parameters]);
    }

    /**
     * Make a POST request.
     *
     * @param string $path
     * @param mixed  $body
     * @param array  $headers
     *
     * @return \Guzzle\Http\Message\Request
     */
    public function post($path, $body = null, array $headers = [])
    {
        return $this->request($path, $body, 'POST', $headers);
    }

    /**
     * Send request with HTTP client.
     *
     * @param string $path
     * @param mixed  $body
     * @param string $method
     * @param array  $headers
     * @param array  $options
     *
     * @return \Guzzle\Http\Message\Response
     */
    public function request($path, $body = null, $method = 'GET', array $headers = [], array $options = [])
    {
        $options = $this->sign($options, $method, $path);

        $request = $this->createRequest($method, $path, $body, $headers, $options);

        try {
            $response = $this->client->send($request);
        } catch (ClientErrorResponseException $e) {
            var_dump($e->getMessage());
            die;
        } catch (\RuntimeException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    /**
     * Create request with HTTP client.
     *
     * @param string $method
     * @param string $path
     * @param mixed  $body
     * @param array  $headers
     * @param array  $options
     *
     * @return \Guzzle\Http\Message\Request
     */
    protected function createRequest($method, $path, $body = null, array $headers = [], array $options = [])
    {
        return $this->client->createRequest(
            $method,
            $path,
            array_merge($this->headers, $headers),
            $body,
            $options
        );
    }

    /**
     * Sign the request with the set user.
     *
     * @param array  $options
     * @param string $method
     * @param string $path
     *
     * @return array
     */
    public function sign(array $options, $method, $path)
    {
        $query = array_key_exists('query', $options) ? $options['query'] : [];

        // Sets the additional data to include in the signature. Method and path
        // is added to prevent that a man in the middle attack could potentially
        // modify the endpoint or HTTP method.
        $signer = [
          'method' => $method,
          'path' => $path,
          'username'  => 'system',
          'timestamp' => time()
        ];

        $query = array_merge($query, $signer);

        // Create the signature token
        ksort($query, SORT_STRING);
        $string = implode('-', $query);
        $token = hash_hmac('sha256', strtolower($string), $this->secret);

        // And attach the token to the query
        $query['token'] = $token;
        $options['query'] = $query;

        return $options;
    }
}
