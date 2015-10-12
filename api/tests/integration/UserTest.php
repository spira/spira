<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Cache;
use App\Models\UserCredential;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserTest.
 * @group integration
 */
class UserTest extends TestCase
{
    use MailcatcherTrait;

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

    public function testNoUserInToken()
    {
        $user = $this->createUser();
        $this->assignAdmin($user);
        $userToGet = $this->createUser();
        $token = $this->tokenFromUser($user, ['_user' => '', 'sub' => false]);

        $this->getJson('/users/'.$userToGet->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testGetAllPaginatedByAdminUser()
    {
        $this->createUsers(10);
        $user = $this->createUser();
        $this->assignAdmin($user);
        $token = $this->tokenFromUser($user);

        $this->getJson('/users', [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'Range' => 'entities=0-19',
        ]);
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
    }

    public function testGetAllPaginatedByGuestUser()
    {
        $user = $this->createUser();

        $token = $this->tokenFromUser($user);

        $this->getJson('/users', [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'Range' => 'entities=0-19',
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testGetOneByAdminUser()
    {
        $user = $this->createUser();
        $this->assignAdmin($user);
        $userToGet = $this->createUser();
        $token = $this->tokenFromUser($user, ['_user' => '']);

        $this->getJson('/users/'.$userToGet->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
    }

    public function testGetOneByGuestUser()
    {
        $user = $this->createUser();
        $userToGet = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->getJson('/users/'.$userToGet->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testGetOneBySelfUser()
    {
        $user = $this->createUser();
        $userToGet = $user;
        $token = $this->tokenFromUser($user);

        $this->getJson('/users/'.$userToGet->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
    }

    public function testGetProfileByGuestUser()
    {
        $this->markTestSkipped('Permissions have not been implemented yet.');

        $user = $this->createUser();
        $userToGet = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->getJson('/users/'.$userToGet->user_id.'/profile', [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testPutOne()
    {
        $user = $this->getFactory(User::class)
            ->showOnly(['user_id', 'username', 'email', 'first_name', 'last_name'])
            ->append(
                '_userCredential',
                $this->getFactory(UserCredential::class)
                    ->hide(['self'])
                    ->makeVisible(['password'])
                    ->customize(['password' => 'password'])
                    ->toArray()
            )
            ->transformed();

        $this->putJson('/users/'.$user['userId'], $user);

        $response = json_decode($this->response->getContent());

        $createdUser = User::find($user['userId']);

        $this->assertResponseStatus(201);
        $this->assertEquals($user['firstName'], $createdUser->first_name);
        $this->assertObjectNotHasAttribute('_userCredential', $response);
        $this->assertObjectNotHasAttribute('_userProfile', $response);
    }

    public function testPutOneNoCredentials()
    {
        $user = $this->getFactory(User::class)
            ->showOnly(['user_id', 'email', 'first_name', 'last_name'])
            ->transformed();

        $this->putJson('/users/'.$user['userId'], $user);

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
    }

    public function testPutOneAlreadyExisting()
    {
        $user = $this->createUser();
        $user['_userCredential'] = ['password' => 'password'];

        $user = $this->getFactory(User::class)
            ->setModel($user)
            ->hide(['_self'])
            ->transformed();

        $this->putJson('/users/'.$user['userId'], $user);

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
    }

    public function testPatchOneByAdminUser()
    {
        $user = $this->createUser();
        $this->assignAdmin($user);
        $userToUpdate = $this->createUser();
        $token = $this->tokenFromUser($user);

        $update = [
            'firstName' => 'foobar',
        ];

        $this->patchJson('/users/'.$userToUpdate->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $updatedUser = User::find($userToUpdate->user_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $updatedUser->first_name);
    }

    public function testPatchOneByAdminUserPassword()
    {
        $user = $this->createUser();
        $this->assignAdmin($user);
        $userToUpdate = $this->createUser();
        $token = $this->tokenFromUser($user);

        $update = [
            '_userCredential' => [
                'password' => 'foobarfoobar',
            ],
        ];

        $this->patchJson('/users/'.$userToUpdate->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $updatedCredentials = UserCredential::find($userToUpdate->user_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertTrue(Hash::check('foobarfoobar', $updatedCredentials->password));
    }

    public function testPatchOneBySelfUserPassword()
    {
        $user = $this->createUser();
        $userToUpdate = $user;
        $token = $this->tokenFromUser($user);

        $update = [
            '_userCredential' => [
                'password' => 'foobarfoobar',
            ],
        ];

        $this->patchJson('/users/'.$userToUpdate->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $updatedCredentials = UserCredential::find($userToUpdate->user_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertTrue(Hash::check('foobarfoobar', $updatedCredentials->password));

        // Assert token is invalid

        /** @var \Spira\Auth\Driver\Guard $auth */
        $auth = $this->app['auth'];
        $payload = $auth->getTokenizer()->decode($token);
        $this->setExpectedException(\Spira\Auth\Token\TokenExpiredException::class, 'Token has expired');
        $auth->getBlacklist()->check($payload);
    }

    public function testPatchOneByGuestUser()
    {
        $user = $this->createUser();
        $userToUpdate = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->patchJson('/users/'.$userToUpdate->user_id, [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testPatchOneBySelfUser()
    {
        $user = $this->createUser();
        $userToUpdate = $user;
        $token = $this->tokenFromUser($user);

        $update = [
            'firstName' => 'foobar',
        ];

        $this->patchJson('/users/'.$userToUpdate->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $updatedUser = User::find($userToUpdate->user_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals('foobar', $updatedUser->first_name);
    }

    public function testPatchRegionCodeInvalid()
    {
        $user = $this->createUser();
        $userToUpdate = $user;
        $token = $this->tokenFromUser($user);

        $update = [
            'regionCode' => 'zz', //an invalid region
        ];

        $this->patchJson('/users/'.$userToUpdate->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $updatedUser = User::find($userToUpdate->user_id);

        $this->assertResponseStatus(422);
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('regionCode', $object->invalid);

        $this->assertEquals($user->region_code, $updatedUser->region_code);
    }

    public function testPatchOneBySelfUserUUID()
    {
        $user = $this->createUser();
        $userToUpdate = $user;
        $token = $this->tokenFromUser($user);

        $update = [
            'userId' => '1234',
            'firstName' => 'foobar',
        ];

        $this->patchJson('/users/'.$userToUpdate->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $this->assertResponseStatus(400);
    }

    public function testDeleteOneByAdminUser()
    {
        $user = $this->createUser();
        $this->assignAdmin($user);
        $userToDelete = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->deleteJson('/users/'.$userToDelete->user_id, [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $user = User::find($userToDelete->user_id);
        $profile = UserProfile::find($userToDelete->user_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertNull($user);
        $this->assertNull($profile);
    }

    public function testDeleteOneByGuestUser()
    {
        $user = $this->createUser();
        $userToDelete = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->deleteJson('/users/'.$userToDelete->user_id, [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $user = User::find($userToDelete->user_id);

        $this->assertResponseStatus(403);
        $this->assertNotNull($user);
    }

    public function testResetPasswordMail()
    {
        $this->clearMessages();
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->deleteJson('/users/'.$user->email.'/password', [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $mail = $this->getLastMessage();

        $this->assertResponseStatus(202);
        $this->assertResponseHasNoContent();
        $this->assertContains('Password', $mail->subject);

        // Additional testing, to ensure that the token sent, can only be used
        // one time.

        // Extract the token from the message source
        $msg = $this->getLastMessage();
        $source = $this->getMessageSource($msg->id);
        preg_match_all('/https?:\/\/\S(?:(?![\'"]).)*/', $source, $matches);
        $tokenUrl = trim($matches[0][0]);
        $parsed = parse_url($tokenUrl);
        $token = str_replace('loginToken=', '', $parsed['query']);

        // Use it the first time
        $this->getJson('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $this->assertResponseOk();

        // Use it the second time
        $this->getJson('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $this->assertException('Invalid', 422, 'TokenInvalidException');
    }

    public function testResetPasswordMailInvalidEmail()
    {
        $this->clearMessages();
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->deleteJson('/users/foo.bar.'.$user->email.'/password', [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $this->assertResponseStatus(404);
    }

    public function testChangeEmail()
    {
        $this->clearMessages();
        $user = $this->createUser();
        // Ensure that the current email is considered confirmed.
        $user->email_confirmed = date('Y-m-d H:i:s');
        $user->save();
        $token = $this->tokenFromUser($user);
        // Make a request to change email
        $update = ['email' => 'foo@bar.com'];
        $this->patchJson('/users/'.$user->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        // Ensure that we get the right response
        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        // Check the sent email and ensure the user's email address hasn't changed yet
        $updatedUser = User::find($user->user_id);
        $mail = $this->getLastMessage();
        $this->assertContains('<foo@bar.com>', $mail->recipients);
        $this->assertNull($updatedUser->email_confirmed);
        $this->assertContains('Confirm', $mail->subject);
        // Get the token in the URL link
        $source = $this->getMessageSource($mail->id);
        preg_match_all('/https?:\/\/\S(?:(?![\'"]).)*/', $source, $matches);
        $tokenUrl = $matches[0][0];
        $parsed = parse_url($tokenUrl);
        $tokens = explode('&amp;', $parsed['query']);
        $emailToken = str_replace('emailConfirmationToken=', '', $tokens[0]);
        $loginToken = str_replace('loginToken=', '', $tokens[1]);
        // Ensure the login token is valid
        $this->assertEquals($user->user_id, Cache::get('login_token_'.$loginToken, false));
        // Confirm the email change
        $datetime = date(\DateTime::ISO8601);
        $update = ['emailConfirmed' => $datetime];
        $this->patchJson('/users/'.$user->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'email-confirm-token' => $emailToken,
        ]);
        // Ensure we get the right response
        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        // Check to see if the email has changed correctly
        $updatedUser = User::find($user->user_id);
        $this->assertEquals($datetime, date(\DateTime::ISO8601, strtotime($updatedUser->email_confirmed)));
        $this->assertEquals('foo@bar.com', $updatedUser->email);
    }

    public function testUpdateEmailConfirmed()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);
        $datetime = date('Y-m-d H:i:s');
        $update = ['emailConfirmed' => $datetime];
        // For the purposes of this test, the old email does not matter.
        $emailToken = $user->createEmailConfirmToken($user->email, 'foo@bar.com');
        $this->patchJson('/users/'.$user->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'Email-Confirm-Token' => $emailToken,
        ]);
        $updatedUser = User::find($user->user_id);
        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($datetime, date('Y-m-d H:i:s', strtotime($updatedUser->email_confirmed)));
    }

    public function testUpdateEmailConfirmedInvalidToken()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);
        $datetime = date('Y-m-d H:i:s');
        $update = ['emailConfirmed' => $datetime];
        $emailToken = 'foobar';
        $this->patchJson('/users/'.$user->user_id, $update, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'Email-Confirm-Token' => $emailToken,
        ]);
        $this->assertResponseStatus(422);
    }
}
