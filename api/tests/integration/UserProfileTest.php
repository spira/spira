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

/**
 * Class UserProfileTest.
 * @group integration
 */
class UserProfileTest extends TestCase
{
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
        UserProfile::flushEventListeners();
        UserProfile::boot();
    }

    public function testGetOne()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $user->userProfile()->save($this->getFactory(UserProfile::class)->make());

        $this->getJson('/users/'.$user->user_id.'/profile', ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatus(200);

        $response = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('website', $response);
        $this->assertEquals($user->userProfile->website, $response->website);
    }

    public function testPutOne()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $userProfile = $this->getFactory(UserProfile::class)->make([
            'website' => 'http://some-website.com',
        ]);
        $userProfile->user_id = $user->user_id;

        $profileTransformed = $this->getFactory(UserProfile::class)->setModel($userProfile)->transformed();

        $this->putJson('/users/'.$user->user_id.'/profile', $profileTransformed, ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatus(201);

        /** @var User $createdUser */
        $createdUser = User::findOrFail($user->user_id);

        $addedProfile = $createdUser->userProfile;

        $this->assertEquals($addedProfile->user_id, $createdUser->user_id);
        $this->assertEquals($profileTransformed['website'], $addedProfile->website);
    }

    public function testPatchOne()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $user->userProfile()->save($this->getFactory(UserProfile::class)->make());

        $user->userProfile->website = 'http://example.com';

        $profileTransformed = $this->getFactory(UserProfile::class)->setModel($user->userProfile)->transformed();

        $this->patchJson('/users/'.$user->user_id.'/profile', $profileTransformed, ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);

        $this->assertResponseStatus(204);

        $updatedUser = User::find($user->user_id);

        $updatedProfile = $updatedUser->userProfile;

        $this->assertEquals($updatedProfile->website, 'http://example.com');
    }
}
