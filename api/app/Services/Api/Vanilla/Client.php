<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services\Api\Vanilla;

use InvalidArgumentException;
use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Exception\RequestException;
use App\Services\Api\Vanilla\Api\ApiAbstract;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

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
     * The user to operate with. Defaults to Vanilla's internal system user.
     *
     * @var array
     */
    protected $user = [
        'username' => 'system',
        'email' => 'system@domain.com',
    ];

    /**
     * Map group name to class names.
     *
     * @var array
     */
    protected $map = [
        'configuration' => 'Configuration',
        'comments' => 'Comment',
        'discussions' => 'Discussion',
        'users' => 'User',
    ];

    /**
     * Assign dependencies.
     *
     * @param  GuzzleClient $client
     *
     * @return void
     */
    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;

        $this->secret = env('VANILLA_API_SECRET');
        $this->baseUrl = $this->getForumHost();

        $this->client->setBaseUrl($this->baseUrl);
        $this->client->getEventDispatcher()->addListener(
            'request.error',
            [new Error, 'onRequestError']
        );
    }

    /**
     * Retrieve the API group to call a method within.
     *
     * @api
     *
     * @param  string $group
     *
     * @throws InvalidArgumentException
     *
     * @return ApiInterface|ApiAbstract
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
     * @internal
     *
     * @param  string $path
     * @param  array  $parameters
     * @param  array  $headers
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
     * @internal
     *
     * @param  string $path
     * @param  mixed  $body
     * @param  array  $headers
     *
     * @return \Guzzle\Http\Message\Request
     */
    public function post($path, $body = null, array $headers = [])
    {
        return $this->request($path, $body, 'POST', $headers);
    }

    /**
     * Make a PUT request.
     *
     * @internal
     *
     * @param  string $path
     * @param  mixed  $body
     * @param  array  $headers
     *
     * @return \Guzzle\Http\Message\Request
     */
    public function put($path, $body, array $headers = [])
    {
        return $this->request($path, $body, 'PUT', $headers);
    }

    /**
     * Make a PUT request.
     *
     * @internal
     *
     * @param  string $path
     * @param  mixed  $body
     * @param  array  $headers
     *
     * @return \Guzzle\Http\Message\Request
     */
    public function delete($path, $body = null, array $headers = [])
    {
        return $this->request($path, $body, 'DELETE', $headers);
    }

    /**
     * Send request with HTTP client.
     *
     * @internal
     *
     * @param  string $path
     * @param  mixed  $body
     * @param  string $method
     * @param  array  $headers
     * @param  array  $options
     *
     * @throws ServiceUnavailableHttpException
     *
     * @return \Guzzle\Http\Message\Response
     */
    public function request($path, $body = null, $method = 'GET', array $headers = [], array $options = [])
    {
        $options = $this->sign($options, $method, $path);
        $request = $this->createRequest($method, $path, $body, $headers, $options);

        try {
            $response = $this->client->send($request);
        } catch (RequestException $e) {
            throw new ServiceUnavailableHttpException(null, $e->getMessage(), $e, $e->getCode());
        }

        return $response;
    }

    /**
     * Create request with HTTP client.
     *
     * @param  string $method
     * @param  string $path
     * @param  mixed  $body
     * @param  array  $headers
     * @param  array  $options
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
     * @internal
     *
     * @param  array  $options
     * @param  string $method
     * @param  string $path
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
            'username'  => $this->user['username'],
            'email'     => $this->user['email'],
            'timestamp' => time(),
        ];

        // Strip away any empty records (username/email might not be provided)
        $signer = array_filter($signer);

        // Merge them with potential other options
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

    /**
     * Set the Vanilla user to operate with.
     *
     * @api
     *
     * @param  string $username
     * @param  string $email
     *
     * @return void
     */
    public function setUser($username = null, $email = null)
    {
        $this->user = [
            'username' => $username,
            'email' => $email,
        ];
    }

    /**
     * Get the forum host url.
     * @return string
     */
    protected function getForumHost()
    {
        //try the forumserver first, then default to upstream (used by the runtime)
        $host = strlen(env('FORUMSERVER_HOST') > 0) ? env('FORUMSERVER_HOST') : env('UPSTREAM_WEB_TCP_ADDR');

        return sprintf('http://%s:%s/api/', $host, env('FORUMSERVER_PORT'));
    }
}
