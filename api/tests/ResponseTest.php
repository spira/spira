<?php

use Spira\Responder\Response\ApiResponse;

class ResponseTest extends TestCase
{
    public function testRedirectNoUrl()
    {
        $this->setExpectedExceptionRegExp(
            InvalidArgumentException::class,
            '/redirect.*/',
            0
        );
        $response = new ApiResponse;

        $response->redirect('');
    }

    public function testRedirectIncorrectStatusCode()
    {
        $this->setExpectedExceptionRegExp(
            InvalidArgumentException::class,
            '/redirect.*/',
            0
        );
        $response = new ApiResponse;

        $response->redirect('http://foo.bar', 200);
    }
}
