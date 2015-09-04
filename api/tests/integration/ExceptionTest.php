<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;

/**
 * Class RestExceptionTest.
 * @group integration
 */
class RestExceptionTest extends TestCase
{
    /**
     * Invalid route test.
     */
    public function testInvalidRoute()
    {
        $this->getJson('/this-url-does-not-exist');

        $this->assertResponseStatus(404);
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('message', $object);
        $this->assertTrue(is_string($object->message), 'message attribute is text');
    }

    /**
     * Internal exception test.
     */
    public function testInternalException()
    {
        $this->getJson('/test/internal-exception');

        $this->assertResponseStatus(500);
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('message', $object);
        $this->assertTrue(is_string($object->message), 'message attribute is text');
    }

    /**
     * Fatal exception tests. Uses guzzle to avoid the fatal exception halting phpunit.
     */
    public function testFatalError()
    {
        $webserverIp = getenv('WEBSERVER_HOST');
        $webserverPort = getenv('WEBSERVER_PORT');

        $request = new Client([
            'base_url' => "http://$webserverIp:$webserverPort",
        ]);

        try {
            $response = $request->get('/test/fatal-error');
            $statusCode = $response->getStatusCode();
            $this->fail('Expected exception GuzzleHttp\Exception\ServerException not thrown. Status code was '.$statusCode);
        } catch (ServerException $e) {
            $response = $e->getResponse();

            $object = json_decode($response->getBody());

            $this->assertTrue(is_object($object), 'Response is an object');

            $this->assertObjectHasAttribute('message', $object);
            $this->assertTrue(is_string($object->message), 'message attribute is text');
        }
    }
}
