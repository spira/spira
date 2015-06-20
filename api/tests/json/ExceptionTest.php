<?php

class RestExceptionTest extends TestCase
{
    protected $entity;

    public function setUp()
    {
        parent::setUp();

    }

    /**
     * Invalid route test
     */
    public function testInvalidRoute()
    {
        $this->get('/this-url-does-not-exist');

        $this->assertResponseStatus(404);
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('message', $object);
        $this->assertTrue(is_string($object->message), 'message attribute is text');

    }

    /**
     * Internal exception test
     */
    public function testInternalException()
    {
        $this->get('/test/internal-exception');

        $this->assertResponseStatus(500);
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('message', $object);
        $this->assertTrue(is_string($object->message), 'message attribute is text');

    }

}
