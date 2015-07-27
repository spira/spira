<?php

use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Article::class, 10)->create();

//        factory(App\Models\Article::class)->create()
//            ->permalink()->save(factory(App\Models\ArticlePermalink::class)->make())
//        ;

    }
}
