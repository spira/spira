<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\AbstractPost;
use App\Models\PostComment;
use App\Models\PostPermalink;
use App\Models\PostSectionsDisplay;
use App\Models\Section;
use App\Models\Tag;
use App\Models\Meta;
use App\Models\User;
use App\Models\Image;
use Spira\Model\Collection\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Faker\Factory as Faker;

abstract class AbstractPostSeeder extends BaseSeeder
{
    protected $class;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->class = $this->getClass();

        $this->command->comment('Seeding '.$this->class);

        $images = Image::all();

        $users = User::all();

        $faker = Faker::create('au_AU');

        $supportedRegions = array_pluck(config('regions.supported'), 'code');

        $tags = factory(Tag::class, 30)->create();

        $groupedTagPivots = $this->getGroupTagPivots($tags);


        $className = $this->class;
        factory($className, 50)
            ->create()
            ->each(function (AbstractPost $post) use ($images, $users, $tags, $groupedTagPivots, $faker, $supportedRegions) {

                //add sections
                /** @var \Illuminate\Database\Eloquent\Collection $sections */
                $sections = factory(Section::class, rand(2, 8))->make();
                $post->sections()->saveMany($sections);

                $post->sections_display = factory(PostSectionsDisplay::class)
                    ->make([
                        'sort_order' => array_map(function (Section $contentPiece) {
                            return $contentPiece->getKey();
                        }, $sections->reverse()->all()),
                    ]);

                //add localizations
                $region = $faker->randomElement($supportedRegions);

                $post->localizations()->create([
                    'region_code' => $region,
                    'localizations' => [
                        'title' => $faker->sentence,
                        'excerpt' => $faker->paragraph(),
                    ],
                ])->save();

                $post->save();

                //add a meta tag
                $post->metas()->save(factory(Meta::class)->make());

                //add permalinks
                $permalinks = factory(PostPermalink::class, 2)->make()->all();
                $post->permalinks()->saveMany($permalinks);

                //add thumbnail
                $post->thumbnail_image_id = $images->random(1)->getKey();

                //add tags
                $post->tags()->sync($groupedTagPivots->random(rand(2, 5))->toArray());

                //add comments
                $this->randomElements(factory(PostComment::class, 10)->make())
                    ->each(function (PostComment $comment) use ($post, $users) {

                        try {
                            $post->comments()->save($comment->toArray(), $users->random());
                        } catch (HttpException $e) {
                            echo 'Caught exception'.get_class($e).' : '.$e->getMessage()."\n\n"; //@todo resolve why this occurs
                            // Likely not to do with the content of the comment as it still occurs in a random fashion when the same 5 comments are added to all posts
                            // Likely not to do with rate limiting as I placed a usleep(1000000); in the try block above and it still threw bad request errors
                            echo 'Comment: '.json_encode($comment->toArray())."\n\n";
                        }
                    });

                $userCount = rand(2, $users->count());
                /** @var Collection $users */
                $users = $users->random($userCount);
                for ($i = 0; $i < $userCount; $i++) {
                    $user = $users->pop();
                    $post->bookmarks()->save(factory(App\Models\Bookmark::class)->make(['user_id' => $user->user_id]));
                    $post->userRatings()->save(factory(App\Models\Rating::class)->make(['user_id' => $user->user_id]));
                }

                $post->save();
            });
    }

    abstract protected function getClass();

    abstract protected function getGroupTagPivots($tags);
}
