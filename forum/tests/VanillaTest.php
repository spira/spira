<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use GuzzleHttp\Client;

class VanillaTest extends PHPUnit_Framework_TestCase
{
    protected function getEnvWithFallback($variable, $fallbackVariable)
    {
        return getenv($variable) ?: $fallbackVariable;
    }

    public function testForumInstalled()
    {
        $client = new Client([
            'base_url' => sprintf(
                'http://%s:%s',
                $this->getEnvWithFallback('VANILLA_SERVER_HOST', 'web'),
                getenv('VANILLA_SERVER_PORT')
            ),
        ]);

        $response = $client->get('/');
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Recent Discussions', $body);
        $this->assertContains('Community Software by Vanilla Forums', $body);
    }
}
