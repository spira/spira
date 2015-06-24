<?php

class IndexTest extends TestCase
{

    /**
     * Index page (Apiary documentation) test
     */
    public function testIndexPage()
    {
        $this->get('/');

        $this->assertResponseOk();

        $this->see('<title>Spira - API Documentation</title>');
    }


}
