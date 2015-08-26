<?php

use App\Models\Article;
use App\Models\ArticleMeta;
use App\Models\Image;
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
        Article::removeAllFromIndex(); //clear all entries in elastic search

        factory(Article::class, 50)
            ->create()
            ->each(function (Article $article) {
                $permalinks = factory(ArticlePermalink::class, rand(0, 4))->make()->all();
                $metas = factory(ArticleMeta::class, 2)->make()->all();
                $tags = factory(Tag::class, 2)->make()->all();
                $image1 = $entity = factory(Image::class)->create();
                $image2 = $entity = factory(Image::class)->create();
                $image3 = $entity = factory(Image::class)->create();
                $article->save();
                $article->metas()->saveMany($metas);
                $article->permalinks()->saveMany($permalinks);
                $article->tags()->saveMany($tags);
                $article->images()->save($image1,['group_type'=>'primary']);
                $article->images()->save($image2,['group_type'=>'thumbnail']);
                $article->images()->save($image3,['group_type'=>'carousel']);
            })
        ;

        Article::addAllToIndex(); //push all articles to elastic search
    }
}
