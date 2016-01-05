<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Tag;
use App\Models\Meta;
use App\Models\User;
use App\Models\Image;
use Spira\Core\Model\Collection\Collection;
use App\Models\Section;
use App\Models\PostComment;
use Faker\Factory as Faker;
use App\Models\AbstractPost;
use App\Models\PostPermalink;
use App\Models\PostSectionsDisplay;
use App\Services\Api\Vanilla\Client as VanillaClient;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class AbstractPostSeeder extends BaseSeeder
{
    protected $class;
    protected $addComments = false;

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

        /** @var ProgressBar $progressBar */
        $progressBar = $this->command->getOutput()->createProgressBar(50);

        $className = $this->class;

        /** @var $discussionsApi \App\Services\Api\Vanilla\Api\Discussion */
        $discussionsApi = App::make(VanillaClient::class)->api('discussions');

        factory($className, 50)
            ->create()
            ->each(function (AbstractPost $post) use ($progressBar, $images, $users, $tags, $groupedTagPivots, $faker, $supportedRegions, $className, $discussionsApi) {

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

                if ($this->addComments) {
                    //add comments
                    try {
                        $discussionsApi->toggleSpamCheck(false);

                        $this->randomElements(factory(PostComment::class, 5)->make())
                            ->each(
                                function (PostComment $comment) use ($post, $users) {
                                    $post->comments()->save($comment->toArray(), $users->random());
                                }
                            );
                    } catch (HttpException $e) {
                        echo 'Caught exception "' . get_class($e) . '": ' . $e->getMessage() . "\n";
                    } finally {
                        $discussionsApi->toggleSpamCheck(true);
                    }
                }

                $userCount = rand(2, $users->count());
                /** @var Collection $users */
                $users = $users->random($userCount);
                for ($i = 0; $i < $userCount; $i++) {
                    $user = $users->pop();
                    $post->bookmarks()->save(factory(App\Models\Bookmark::class)->make(['user_id' => $user->user_id]));
                    $post->userRatings()->save(factory(App\Models\Rating::class)->make(['user_id' => $user->user_id]));
                }

                $post->save();

                $progressBar->advance();
            });

        $progressBar->finish();
        $this->command->line('');
    }

    abstract protected function getClass();

    abstract protected function getGroupTagPivots($tags);
}
