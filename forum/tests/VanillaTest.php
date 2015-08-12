<?php

use GuzzleHttp\Client;

class VanillaTest extends PHPUnit_Framework_TestCase
{
    protected function getEnvWithFallback($variable, $fallbackVariable)
    {
        return getenv($variable) ?: getenv($fallbackVariable);
    }

    public function testForumInstalled()
    {
        $client = new Client([
            'base_url' => sprintf(
                'http://%s:%s',
                $this->getEnvWithFallback('VANILLA_SERVER_HOST', 'WEB_PORT_8008_TCP_ADDR'),
                getenv('VANILLA_SERVER_PORT')
            ),
        ]);

        $response = $client->get('/');
        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Recent Discussions', $body);
        $this->assertContains('BAM! You’ve got a sweet forum', $body);
    }
}
