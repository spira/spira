<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UserStorySeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

        User::fakeUser([
            'email'=>'john.smith@example.com'
        ]);

        foreach(range(0, 99) as $index){

            User::fakeUser();

        }

	}

}
