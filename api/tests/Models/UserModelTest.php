<?php

use App\Models\User;
use App\Models\SocialLogin;

class UserModelTest extends TestCase
{
    public function testUpdatingSocialLogin()
    {
        $user = $this->createUser();
        $socialLogin = factory(SocialLogin::class)->make();
        $user->addSocialLogin($socialLogin);

        // Get a new social login, with the same provider
        $newSocialLogin = factory(SocialLogin::class)->make();
        $newSocialLogin->provider = $socialLogin->provider;
        $user->addSocialLogin($newSocialLogin);

        // Retrieve the user from DB, to make assertions against
        $user = User::find($user->user_id);
        $logins = $user->socialLogins->all();

        $this->assertCount(1, $logins);
        $this->assertEquals($socialLogin->provider, head($logins)->provider);
        $this->assertEquals($newSocialLogin->token, head($logins)->token);
    }

    public function testAdditionalSocialLogin()
    {
        $user = $this->createUser();
        $socialLogin = factory(SocialLogin::class)->make();
        $newSocialLogin = factory(SocialLogin::class)->make();

        // Make sure we have different providers
        $socialLogin->provider = 'facebook';
        $newSocialLogin->provider = 'google';

        $user->addSocialLogin($socialLogin);
        $user->addSocialLogin($newSocialLogin);

        // Retrieve the user from DB, to make assertions against
        $user = User::find($user->user_id);
        $logins = $user->socialLogins->all();

        $this->assertCount(2, $logins);
    }
}
