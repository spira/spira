<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function testLogin()
    {
        $user = factory(App\Models\User::class)->create();

        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'password',
        ]);

        $array = json_decode($this->response->getContent(), true);

        $this->assertResponseOk();
        $this->assertEquals('application/json', $this->response->headers->get('content-type'));
        $this->assertArrayHasKey('token', $array);
        $this->assertArrayHasKey('iss', $array['decodedTokenBody']);
        $this->assertArrayHasKey('userId', $array['decodedTokenBody']['#user']);
    }

    public function testFailedLogin()
    {
        // $this->setExpectedException('App\Exceptions\ValidationException');

        $user = factory(App\Models\User::class)->create();

        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'foobar',
        ]);

        $this->assertResponseStatus('401');
    }
}
