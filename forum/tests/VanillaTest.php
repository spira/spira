<?php

use GuzzleHttp\Client;

class VanillaTest extends PHPUnit_Framework_TestCase
{
    public function testForumInstalled()
    {
        $client = new Client([
            'base_url' => sprintf(
                'http://%s:%s',
                getenv('VANILLA_SERVER_HOST'),
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
