<?php

use App\Models\User;
use Rhumsaa\Uuid\Uuid;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions, MailcatcherTrait;

    protected function createUser($type = 'admin')
    {
        $user = factory(User::class)->create(['user_type' => $type]);
        return $user;
    }

    public function testGetAllByAdminUser()
    {
        $user = factory(User::class, 10)->create();
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->get('/users', [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
    }

    public function testGetAllByGuestUser()
    {
        $user = $this->createUser('guest');
        $token = $this->tokenFromUser($user);

        $this->get('/users', [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testGetOneByAdminUser()
    {
        $user = $this->createUser();
        $userToGet = $this->createUser('guest');
        $token = $this->tokenFromUser($user);

        $this->get('/users/'.$userToGet->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
    }

    public function testGetOneByGuestUser()
    {
        $user = $this->createUser('guest');
        $userToGet = $this->createUser('guest');
        $token = $this->tokenFromUser($user);

        $this->get('/users/'.$userToGet->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testGetOneBySelfUser()
    {
        $user = $this->createUser('guest');
        $userToGet = $user;
        $token = $this->tokenFromUser($user);

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
        $user = $factory->get(User::class)
            ->showOnly(['user_id', 'email', 'first_name', 'last_name'])
            ->append('_userCredential',
                $factory->get(App\Models\UserCredential::class)
                    ->hide(['self'])
                    ->makeVisible(['password'])
                    ->customize(['password' => 'password'])
                    ->toArray()
                );

        $transformerService = $this->app->make(App\Services\TransformerService::class);
        $transformer = new App\Http\Transformers\IlluminateModelTransformer($transformerService);
        $user = $transformer->transform($user);

        $this->put('/users/'.$user['userId'], $user);

        $response = json_decode($this->response->getContent());

        $createdUser = User::find($user['userId']);
        $this->assertResponseStatus(201);
        $this->assertEquals($user['firstName'], $createdUser->first_name);
        $this->assertObjectNotHasAttribute('_userCredential', $response);
    }

    public function testPutOneAlreadyExisting()
    {
        $user = factory(User::class)->create();
        $user['_userCredential'] = ['password' => 'password'];

        $transformerService = $this->app->make(App\Services\TransformerService::class);
        $transformer = new App\Http\Transformers\IlluminateModelTransformer($transformerService);
        $user = array_except($transformer->transform($user), ['_self', 'userType']);

        $this->put('/users/'.$user['userId'], $user);

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
    }

    public function testPatchOneByAdminUser()
    {
        $user = $this->createUser('admin');
        $userToUpdate = $this->createUser('guest');
        $token = $this->tokenFromUser($user);

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

    public function testPatchOneByGuestUser()
    {
        $user = $this->createUser('guest');
        $userToUpdate = $this->createUser('guest');
        $token = $this->tokenFromUser($user);

        $this->patch('/users/'.$userToUpdate->user_id, [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testPatchOneBySelfUser()
    {
        $user = $this->createUser('guest');
        $userToUpdate = $user;
        $token = $this->tokenFromUser($user);

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
        $userToDelete = $this->createUser('guest');
        $token = $this->tokenFromUser($user);
        $rowCount = User::count();

        $this->delete('/users/'.$userToDelete->user_id, [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, User::count());
    }

    public function testDeleteOneByGuestUser()
    {
        $user = $this->createUser('guest');
        $userToDelete = $this->createUser('guest');
        $token = $this->tokenFromUser($user);
        $rowCount = User::count();

        $this->delete('/users/'.$userToDelete->user_id, [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);

        $this->assertResponseStatus(403);
        $this->assertEquals($rowCount, User::count());
    }

    public function testChangeEmail()
    {
        $this->clearMessages();
        $user = $this->createUser('guest');
        // Ensure that the current email is considered confirmed.
        $user->email_confirmed = date('Y-m-d H:i:s');
        $user->save();
        $token = $this->tokenFromUser($user);
        $update = ['email' => 'foo@bar.com'];
        $this->patch('/users/'.$user->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);
        $updatedUser = User::find($user->user_id);
        $mail = $this->getLastMessage();
        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foo@bar.com', $updatedUser->email);
        $this->assertNull($updatedUser->email_confirmed);
        $this->assertContains('Confirm', $mail->subject);
    }
    public function testUpdateEmailConfirmed()
    {
        $user = $this->createUser('guest');
        $token = $this->tokenFromUser($user);
        $datetime = date('Y-m-d H:i:s');
        $update = ['emailConfirmed' => $datetime];
        $repo = $this->app->make('App\Repositories\UserRepository');
        $emailToken = $repo->makeConfirmationToken($user->email);
        $this->patch('/users/'.$user->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'Email-Confirm-Token' => $emailToken
        ]);
        $updatedUser = User::find($user->user_id);
        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($datetime, $updatedUser->email_confirmed);
    }
    public function testUpdateEmailConfirmedInvalidToken()
    {
        $user = $this->createUser('guest');
        $token = $this->tokenFromUser($user);
        $datetime = date('Y-m-d H:i:s');
        $update = ['emailConfirmed' => $datetime];
        $repo = $this->app->make('App\Repositories\UserRepository');
        $emailToken = 'foobar';
        $this->patch('/users/'.$user->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'Email-Confirm-Token' => $emailToken
        ]);
        $this->assertResponseStatus(422);
    }
}
