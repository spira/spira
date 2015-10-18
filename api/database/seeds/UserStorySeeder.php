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

        $user = $this->createUser([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
            'avatar_img_url' => $faker->imageUrl(100, 100, 'people'),
        ]);

        $adminRole = new \App\Models\Role();
        $adminRole->role_name = 'admin';

        $user->roles()->save($adminRole);

        for ($i = 0; $i < 99; $i++) {
            $user = $this->createUser();
            $this->assignRandomRole($user);
        }
    }

    /**
     * Create a new user with credentials.
     *
     * @param   array   $attributes
     * @return  User
     */
    protected function createUser(array $attributes = [])
    {
        /** @var User $user */
        $user = factory(User::class)
            ->create($attributes);

        $user->userProfile()->save(factory(UserProfile::class)->make());
        $user->setCredential(factory(UserCredential::class)->make());

        return $user;
    }

    protected function assignRandomRole(User $user)
    {
        $role = new \App\Models\Role();
        $role->role_name = (rand(0,1) > 0)?'testrole':'admin';
        $user->roles()->save($role);
    }
}
