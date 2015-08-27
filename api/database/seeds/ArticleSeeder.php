<?php

use App\Models\Article;
use App\Models\ArticleImage;
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
        factory(Article::class, 50)
            ->create()
            ->each(function (Article $article) {
                $permalinks = factory(ArticlePermalink::class, rand(0, 4))->make()->all();
                $metas = factory(ArticleMeta::class, 2)->make()->all();
                $tags = factory(Tag::class, 2)->make()->all();
                $images = $entity = factory(Image::class, 10)->create();
                $article->save();
                $article->metas()->saveMany($metas);
                $article->permalinks()->saveMany($permalinks);
                $article->tags()->saveMany($tags);
                foreach ($images as $image) {
                    $imageArticle = factory(ArticleImage::class)->make();
                    $imageArticle->article_id = $article->article_id;
                    $imageArticle->image_id = $image->image_id;
                    $imageArticle->save();
                }

            })
        ;
    }
}
