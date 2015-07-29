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
        $this->createUser(['email' => 'john.smith@example.com', 'user_type'=>'admin']);

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

        factory(User::class)
            ->create($attributes)
            ->setCredential(factory(UserCredential::class)->make());


    }
}
