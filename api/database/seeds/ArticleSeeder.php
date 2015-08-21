<?php

use App\Models\ArticlePermalink;
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
        //$faker = Faker::create('au_AU');
        factory(App\Models\Article::class, 50)
            ->create()
            ->each(function (\App\Models\Article $article) {
                $permalinks = factory(ArticlePermalink::class, rand(0, 4))->make()->all();
                $metas = factory(\App\Models\ArticleMeta::class, 2)->make()->all();
                $tags = factory(\App\Models\Tag::class, 2)->make()->all();
                $article->save();
                $article->metas()->saveMany($metas);
                $article->permalinks()->saveMany($permalinks);
                $article->tags()->saveMany($tags);
            })
        ;
    }
}
