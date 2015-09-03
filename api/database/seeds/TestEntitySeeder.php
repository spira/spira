<?php

class TestEntitySeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\TestEntity::class, 20)->create();
    }
}
