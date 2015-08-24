<?php

use App\Models\TestEntity;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        //check if an index has been created, and create one if not
        if (!TestEntity::indexExists()) {
            TestEntity::createIndex();
        }

        $this->call('UserStorySeeder');
        $this->command->info('User story seeded!');

        $this->call('TestEntitySeeder');
        $this->command->info('Test entities seeded!');

        $this->call('ArticleSeeder');
        $this->command->info('Articles seeded!');
    }
}
