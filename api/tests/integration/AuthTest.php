<?php

class AuthTest extends TestCase
{
    public function testLogin()
    {
        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => 'foo',
            'PHP_AUTH_PW'   => 'bar',
        ]);

        $this->assertResponseStatus(401);
    }
}
