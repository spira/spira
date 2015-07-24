<?php

use App\Models\User;
use App\Models\UserCredential;
use Rhumsaa\Uuid\Uuid;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
        User::flushEventListeners();
        User::boot();
        UserCredential::flushEventListeners();
        UserCredential::boot();
    }

    protected function createUser($type = 'admin')
    {
        $user = factory(User::class)->create(['user_type' => $type]);
        return $user;
    }

    public function testGetAllByAdminUser()
    {
        factory(User::class, 10)->create();
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
            ->append(
                '_userCredential',
                $factory->get(UserCredential::class)
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

    public function testPutOneNoCredentials()
    {
        $factory = $this->app->make('App\Services\ModelFactory');
        $user = $factory->get(User::class)
            ->showOnly(['user_id', 'email', 'first_name', 'last_name']);

        $transformerService = $this->app->make(App\Services\TransformerService::class);
        $transformer = new App\Http\Transformers\IlluminateModelTransformer($transformerService);
        $user = $transformer->transform($user);

        $this->put('/users/'.$user['userId'], $user);

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
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
}
