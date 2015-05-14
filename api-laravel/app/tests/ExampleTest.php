<?php

class ExampleTest extends TestCase {



    public function testGetAllUsers()
    {
        $crawler = $this->client->request('GET', '/users');

        $this->assertTrue($this->client->getResponse()->isOk());
    }

}
