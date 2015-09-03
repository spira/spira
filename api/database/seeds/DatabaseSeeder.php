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
        if (TestEntity::indexExists()) {
            TestEntity::deleteIndex();
            $this->command->info('ElasticSearch index deleted');
        }
        TestEntity::createIndex();
        $this->command->info('ElasticSearch index created');


        $this->call('ImageSeeder');
        $this->command->info('Images seeded!');

        $this->call('UserStorySeeder');
        $this->command->info('User story seeded!');

        $this->call('TestEntitySeeder');
        $this->command->info('Test entities seeded!');

        $this->call('ArticleSeeder');
        $this->command->info('Articles seeded!');
    }
}
