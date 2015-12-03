<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\tests;

use Spira\Core\Responder\Response\ApiResponse;

class ResponseTest extends TestCase
{
    public function testRedirect()
    {
        $url = 'http:://foo.bar';
        $response = new ApiResponse;
        $response->redirect($url);

        $this->assertEquals('302', $response->getStatusCode());
        $this->assertEquals($url, $response->headers->get('location'));
    }

    public function testCreatedWithRedirect()
    {
        $url = 'http:://foo.bar';
        $response = new ApiResponse;
        $response->created($url);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($url, $response->headers->get('location'));
    }

    public function testRedirectNoUrl()
    {
        $this->setExpectedExceptionRegExp(
            \InvalidArgumentException::class,
            '/redirect.*/',
            0
        );

        (new ApiResponse)->redirect('');
    }

    public function testRedirectIncorrectStatusCode()
    {
        $this->setExpectedExceptionRegExp(
            \InvalidArgumentException::class,
            '/redirect.*/',
            0
        );

        (new ApiResponse)->redirect('http://foo.bar', 200);
    }
}
