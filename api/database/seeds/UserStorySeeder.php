<?php

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
        factory(App\Models\User::class)->create([
            'email' => 'john.smith@example.com',
        ]);

        factory(App\Models\User::class, 99)->create();
    }
}
