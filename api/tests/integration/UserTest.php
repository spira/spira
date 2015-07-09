<?php

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        $this->jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
    }

    protected function assertException($message, $statusCode, $exception)
    {
        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus($statusCode);
        $this->assertContains($message, $body->message);
        $this->assertContains($exception, $body->debug->exception);
    }

    protected function createUser($type = 'admin')
    {
        $user = factory(App\Models\User::class)->make();
        $user->user_type = $type;
        $user->save();

        return $user;
    }

    public function testGetAllByAdminUser()
    {
        $user = $this->createUser();
        $token = $this->jwtAuth->fromUser($user);

        $this->get('/users', [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
    }

    public function testGetAllByPublicUser()
    {
        $user = $this->createUser('public');
        $token = $this->jwtAuth->fromUser($user);

        $this->get('/users', [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testGetOneByAdminUser()
    {
        $user = $this->createUser();
        $userToGet = $this->createUser('public');
        $token = $this->jwtAuth->fromUser($user);

        $this->get('/users/'.$userToGet->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
    }

    public function testGetOneByPublicUser()
    {
        $user = $this->createUser('public');
        $userToGet = $this->createUser('public');
        $token = $this->jwtAuth->fromUser($user);

        $this->get('/users/'.$userToGet->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testGetOneBySelfUser()
    {
        $user = $this->createUser('public');
        $userToGet = $user;
        $token = $this->jwtAuth->fromUser($user);

        $this->get('/users/'.$userToGet->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
    }

    public function testPutOne()
    {
        $factory = $this->app->make('App\Services\ModelFactory');
        $user = $factory->get(\App\Models\User::class)
            ->showOnly(['user_id', 'email', 'first_name', 'last_name'])
            ->append('#userCredential',
                $factory->get(\App\Models\UserCredential::class)
                    ->hide(['self'])
                    ->makeVisible(['password'])
                    ->customize(['password' => 'password'])
                    ->toArray()
                );

        $transformer = $this->app->make('App\Http\Transformers\BaseTransformer');
        $user = $transformer->transform($user);

        $this->put('/users/'.$user['userId'], $user);

        $createdUser = User::find($user['userId']);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($user['firstName'], $createdUser->first_name);
    }

    public function testPatchOneByAdminUser()
    {
        $user = $this->createUser('admin');
        $userToUpdate = $this->createUser('public');
        $token = $this->jwtAuth->fromUser($user);

        $update = [
            'userId' => $userToUpdate->user_id,
            'email' => 'foo@bar.com',
            'firstName' => 'foobar'
        ];

        $this->patch('/users/'.$userToUpdate->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $updatedUser = User::find($userToUpdate->user_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $updatedUser->first_name);
        $this->assertEquals('foo@bar.com', $updatedUser->email);
    }

    public function testPatchOneByPublicUser()
    {
        $user = $this->createUser('public');
        $userToUpdate = $this->createUser('public');
        $token = $this->jwtAuth->fromUser($user);

        $this->patch('/users/'.$userToUpdate->user_id, [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }


    public function testPatchOneBySelfUser()
    {
        $user = $this->createUser('public');
        $userToUpdate = $user;
        $token = $this->jwtAuth->fromUser($user);

        $update = [
            'userId' => $userToUpdate->user_id,
            'email' => 'foo@bar.com',
            'firstName' => 'foobar'
        ];

        $this->patch('/users/'.$userToUpdate->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $updatedUser = User::find($userToUpdate->user_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $updatedUser->first_name);
        $this->assertEquals('foo@bar.com', $updatedUser->email);
    }

    public function testDeleteOneByAdminUser()
    {
        $user = $this->createUser('admin');
        $userToDelete = $this->createUser('public');
        $token = $this->jwtAuth->fromUser($user);
        $rowCount = User::count();

        $this->delete('/users/'.$userToDelete->user_id, [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, User::count());
    }

    public function testDeleteOneByPublicUser()
    {
        $user = $this->createUser('public');
        $userToDelete = $this->createUser('public');
        $token = $this->jwtAuth->fromUser($user);
        $rowCount = User::count();

        $this->delete('/users/'.$userToDelete->user_id, [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertResponseStatus(403);
        $this->assertEquals($rowCount, User::count());
    }
}
