<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Tag;
use App\Models\User;
use App\Models\Image;
use App\Models\Article;
use App\Models\ArticleMeta;
use App\Models\ArticleImage;
use App\Models\ArticleComment;
use App\Models\ArticlePermalink;
use App\Models\ArticleSection;
use App\Models\ArticleSectionsDisplay;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ArticleSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $images = Image::all();

        $users = User::all();

        factory(Article::class, 50)
            ->create()
            ->each(function (Article $article) use ($images, $users) {

                //add sections
                $sections = factory(ArticleSection::class, rand(2, 8))->make();
                $article->sections()->saveMany($sections);

                $article->sections_display = factory(ArticleSectionsDisplay::class)
                    ->make([
                        'sort_order' => array_map(function (ArticleSection $contentPiece) {
                            return $contentPiece->getKey();
                        }, $sections->all()),
                    ]);

                $article->save();

                //add a meta tag
                $article->articleMetas()->save(factory(ArticleMeta::class)->make());

                //add permalinks
                $permalinks = factory(ArticlePermalink::class, 2)->make()->all();
                $article->articlePermalinks()->saveMany($permalinks);

                //add tags
                $tags = factory(Tag::class, 2)->make()->all();
                $article->tags()->saveMany($tags);

                //add comments
                $this->randomElements(factory(ArticleComment::class, 10)->make())
                    ->each(function (ArticleComment $comment) use ($article, $users) {

                        try {
                            $article->comments()->save($comment->toArray(), $users->random());
                        } catch (HttpException $e) {
                            echo 'Caught exception'.get_class($e).' : '.$e->getMessage()."\n\n"; //@todo resolve why this occurs
                            // Likely not to do with the content of the comment as it still occurs in a random fashion when the same 5 comments are added to all articles
                            // Likely not to do with rate limiting as I placed a usleep(1000000); in the try block above and it still threw bad request errors
                            echo 'Comment: '.json_encode($comment->toArray())."\n\n";
                        }
                    });

                $this->randomElements($images)
                    ->each(function (Image $image) use ($article) {
                    factory(ArticleImage::class)->create([
                        'article_id' => $article->article_id,
                        'image_id' => $image->image_id,
                    ]);
                });

            });
    }
}
