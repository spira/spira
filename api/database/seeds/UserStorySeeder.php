<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\User;
use Faker\Factory as Faker;
use App\Models\UserProfile;
use App\Models\UserCredential;

class UserStorySeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('au_AU');

        $this->createUser([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
            'user_type' => 'admin',
            'avatar_img_url' => $faker->imageUrl(100, 100, 'people'),
        ]);

        for ($i = 0; $i < 99; $i++) {
            $this->createUser();
        }
    }

    /**
     * Create a new user with credentials.
     *
     * @param   array   $attributes
     * @return  void
     */
    protected function createUser(array $attributes = [])
    {
        /** @var User $user */
        $user = factory(User::class)
            ->create($attributes);

        $user->userProfile()->save(factory(UserProfile::class)->make());
        $user->setCredential(factory(UserCredential::class)->make());
    }
}
