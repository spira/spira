<?php

use App\Services\Api\Vanilla\Client;

class VanillaIntegrationTest extends TestCase
{
    // Exceptions

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function shouldNotGetApiInstance()
    {
        $client = App::make(Client::class);

        $test = $client->api('do_not_exist');
    }
}
