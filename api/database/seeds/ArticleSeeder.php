<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Section;
use App\Models\Tag;
use App\Models\User;
use App\Models\Image;
use App\Models\Article;
use App\Models\ArticleMeta;
use App\Models\ArticleComment;
use App\Models\ArticlePermalink;
use App\Models\ArticleSectionsDisplay;
use Spira\Model\Collection\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Faker\Factory as Faker;

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

        $faker = Faker::create('au_AU');

        $supportedRegions = array_pluck(config('regions.supported'), 'code');

        $tags = factory(Tag::class, 30)->create();

        $groupedTagPivots = Tag::getGroupedTagPivots($tags, SeedTags::articleGroupTagName);

        factory(Article::class, 50)
            ->create([
                'thumbnail_image_id' => $images->random(1)->getKey(),
            ])
            ->each(function (Article $article) use ($images, $users, $tags, $groupedTagPivots, $faker, $supportedRegions) {

                //add sections
                /** @var \Illuminate\Database\Eloquent\Collection $sections */
                $sections = factory(Section::class, rand(2, 8))->make();
                $article->sections()->saveMany($sections);

                $article->sections_display = factory(ArticleSectionsDisplay::class)
                    ->make([
                        'sort_order' => array_map(function (Section $contentPiece) {
                            return $contentPiece->getKey();
                        }, $sections->reverse()->all()),
                    ]);

                //add localizations
                $region = $faker->randomElement($supportedRegions);

                $article->localizations()->create([
                    'region_code' => $region,
                    'localizations' => [
                        'title' => $faker->sentence,
                        'excerpt' => $faker->paragraph(),
                    ],
                ])->save();

                $article->save();

                //add a meta tag
                $article->articleMetas()->save(factory(ArticleMeta::class)->make());

                //add permalinks
                $permalinks = factory(ArticlePermalink::class, 2)->make()->all();
                $article->articlePermalinks()->saveMany($permalinks);

                //add tags
                $article->tags()->sync($groupedTagPivots->random(rand(2, 5))->toArray());

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

                $userCount = rand(2, $users->count());
                /** @var Collection $users */
                $users = $users->random($userCount);
                for ($i = 0; $i < $userCount; $i++) {
                    $user = $users->pop();
                    $article->bookmarks()->save(factory(App\Models\Bookmark::class)->make(['user_id' => $user->user_id]));
                    $article->userRatings()->save(factory(App\Models\Rating::class)->make(['user_id' => $user->user_id]));
                }

                $article->touch(); // Update search index

            });
    }
}
