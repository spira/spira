<?php

use App\Models\Article;
use App\Models\ArticleMeta;
use App\Models\Tag;
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
        factory(Article::class, 50)
            ->create()
            ->each(function (Article $article) {
                $permalinks = factory(ArticlePermalink::class, rand(0, 4))->make()->all();
                $metas = factory(ArticleMeta::class, 2)->make()->all();
                $tags = factory(Tag::class, 2)->make()->all();
                $article->save();
                $article->metas()->saveMany($metas);
                $article->permalinks()->saveMany($permalinks);
                $article->tags()->saveMany($tags);
            })
        ;
    }
}
