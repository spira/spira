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
 * Class UserProfileTest.
 * @group integration
 */
class UserProfileTest extends TestCase
{

    /**
     * @group failing
     */
    public function testPutOne()
    {

        $this->markTestSkipped("Failing put test ChildEntityController::putOne is expecting a childId");

        $user = $this->createUser();

        $user->userProfile()->save($this->getFactory()->get(UserProfile::class)->make());

        $profileTransformed = $this->getFactory()->get(UserProfile::class)->setModel($user->userProfile)->transformed();

        $this->putJson('/users/'.$user->user_id.'/profile', $profileTransformed);

        $this->assertResponseStatus(201);

        $createdUser = User::find($user->user_id);

        $addedProfile = $createdUser->userProfile;

        $this->assertObjectHasAttribute('user_id', $addedProfile);
        $this->assertEquals($profileTransformed['dob'], $addedProfile->dob);
    }

}
