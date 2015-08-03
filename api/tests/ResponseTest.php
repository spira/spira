<?php

use Spira\Responder\Response\ApiResponse;

class ResponseTest extends TestCase
{
    public function testRedirect()
    {
        $url = 'http:://foo.bar';
        $response = (new ApiResponse)->redirect($url);

        $this->assertEquals('302', $response->getStatusCode());
        $this->assertEquals($url, $response->headers->get('location'));
    }

    public function testRedirectNoUrl()
    {
        $this->setExpectedExceptionRegExp(
            InvalidArgumentException::class,
            '/redirect.*/',
            0
        );

        (new ApiResponse)->redirect('');
    }

    public function testRedirectIncorrectStatusCode()
    {
        $this->setExpectedExceptionRegExp(
            InvalidArgumentException::class,
            '/redirect.*/',
            0
        );

        (new ApiResponse)->redirect('http://foo.bar', 200);
    }
}
