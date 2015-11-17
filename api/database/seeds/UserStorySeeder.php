<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Role;
use App\Models\User;
use App\Models\Image;
use Faker\Factory as Faker;
use App\Models\UserProfile;
use App\Models\UserCredential;

class UserStorySeeder extends BaseSeeder
{
    /** @var \Faker\Generator */
    private $faker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create('au_AU');

        $images = Image::all();

        $user = $this->createUser([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => TestCase::TEST_ADMIN_USER_EMAIL,
            'avatar_img_url' => $this->faker->imageUrl(100, 100, 'people'),
            'avatar_img_id' => $images->random()->image_id,
        ]);

        $user->roles()->sync([Role::SUPER_ADMIN_ROLE, Role::ADMIN_ROLE]);

        $nonAdmin = $this->createUser([
            'first_name' => 'Nick',
            'last_name' => 'Jackson',
            'email' => TestCase::TEST_USER_EMAIL,
            'avatar_img_url' => $this->faker->imageUrl(100, 100, 'people'),
            'avatar_img_id' => $images->random()->image_id,
        ]);

        for ($i = 0; $i < 99; $i++) {
            $user = $this->createUser([
                'avatar_img_id' => $this->faker->optional()->randomElement($images->pluck('image_id')->toArray()),
            ]);

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
        $user->roles()->sync([$this->faker->randomElement(Role::$roles)]);
    }
}
