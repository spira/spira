<?php

use App\Models\User;
use App\Models\UserCredential;
use Illuminate\Database\Seeder;

class UserStorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createUser(['email' => 'john.smith@example.com']);

        for ($i=0; $i < 99; $i++) {
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
        $user = factory(User::class)->create($attributes);
        $credential = factory(UserCredential::class)->make();
        $credential->user_id = $user->user_id;
        $credential->save();
    }
}
