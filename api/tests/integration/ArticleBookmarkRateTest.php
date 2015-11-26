<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\AbstractPost;
use App\Models\Article;

class ArticleBookmarkRateTest extends TestCase
{
    protected $baseRoute = '/articles';
    protected $factoryClass = Article::class;

    protected $ratedName = 'ratedArticles';
    protected $bookmarkedName = 'bookmarkedArticles';

    public function testSimpleRate()
    {
        $user = $this->createUser();
        $post = $this->getFactory($this->factoryClass)->create();
        $rateData = $this->getFactory(App\Models\Rating::class)->transformed();

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson($this->baseRoute.'/'.$post->post_id.'/ratings/'.$rateData['ratingId'], $rateData);

        $this->assertResponseStatus(201);

        $this->assertEquals($user->{$this->ratedName}->first()->post_id, $post->post_id);
        $rating = $user->{$this->ratedName}->first()->pivot->rating_value;
        $this->assertTrue($rating > 0 && $rating < 6);
    }

    public function testInvalidRate()
    {
        $user = $this->createUser();
        $post = $this->getFactory($this->factoryClass)->create();
        $rateData = $this->getFactory(App\Models\Rating::class)->customize(['rating_value' => 6])->transformed();

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson($this->baseRoute.'/'.$post->post_id.'/ratings/'.$rateData['ratingId'], $rateData);

        $this->assertException('There was an issue with the validation of provided entity', 422, 'ValidationException');
    }

    public function testSimpleBookmark()
    {
        $user = $this->createUser();
        $post = $this->getFactory($this->factoryClass)->create();
        $bookmarkData = $this->getFactory(App\Models\Bookmark::class)->transformed();

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson($this->baseRoute.'/'.$post->post_id.'/bookmarks/'.$bookmarkData['bookmarkId'], $bookmarkData);

        $this->assertResponseStatus(201);

        $this->assertEquals($user->{$this->bookmarkedName}->first()->post_id, $post->post_id);
    }

    public function testRemoveRate()
    {
        $user = $this->createUser();
        /** @var AbstractPost $post */
        $post = $this->getFactory($this->factoryClass)->create();
        $rating = $this->getFactory(App\Models\Rating::class)->make(['user_id' => $user->user_id]);
        $post->userRatings()->save($rating);

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->deleteJson($this->baseRoute.'/'.$post->post_id.'/ratings/'.$rating->rating_id);

        $this->assertResponseStatus(204);
        $this->assertEquals(0, $user->{$this->ratedName}->count());
    }

    public function testRemoveBookmark()
    {
        $user = $this->createUser();
        /** @var AbstractPost $post */
        $post = $this->getFactory($this->factoryClass)->create();
        $bookmark = $this->getFactory(App\Models\Bookmark::class)->make(['user_id' => $user->user_id]);
        $post->bookmarks()->save($bookmark);

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->deleteJson($this->baseRoute.'/'.$post->post_id.'/bookmarks/'.$bookmark->bookmark_id);

        $this->assertResponseStatus(204);
        $this->assertEquals(0, $user->{$this->bookmarkedName}->count());
    }

    public function testUpdateRate()
    {
        $user = $this->createUser();
        /** @var AbstractPost $post */
        $post = $this->getFactory($this->factoryClass)->create();
        $rating = $this->getFactory(App\Models\Rating::class)->make(
            [
                'user_id' => $user->user_id,
                'rating_value' => 5,
            ]);

        $post->userRatings()->save($rating);

        $this->assertEquals(5, $post->userRatings->first()->rating_value);
        $this->assertEquals(1, $post->userRatings->count());

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson($this->baseRoute.'/'.$post->post_id.'/ratings/'.$rating->rating_id, [
                'ratingId' => $rating->rating_id,
                'ratingValue' => 2,
        ]);
        $class = $this->factoryClass;
        $post = $class::find($post->post_id);

        $this->assertResponseStatus(201);
        $this->assertEquals(1, $post->userRatings->count());
        $this->assertEquals(2, $post->userRatings->first()->rating_value);
    }

    public function testRemoveRateSpoof()
    {
        $user = $this->createUser();
        /** @var AbstractPost $post */
        $post = $this->getFactory($this->factoryClass)->create();
        $rating = $this->getFactory(App\Models\Rating::class)->make(['user_id' => $user->user_id]);
        $post->userRatings()->save($rating);

        $spoofer = $this->createUser();
        $token = $this->tokenFromUser($spoofer);
        $this->withAuthorization('Bearer '.$token)->deleteJson($this->baseRoute.'/'.$post->post_id.'/ratings/'.$rating->rating_id);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testRemoveBookmarkSpoof()
    {
        $user = $this->createUser();
        /** @var AbstractPost $post */
        $post = $this->getFactory($this->factoryClass)->create();
        $bookmark = $this->getFactory(App\Models\Bookmark::class)->make(['user_id' => $user->user_id]);
        $post->bookmarks()->save($bookmark);

        $spoofer = $this->createUser();
        $token = $this->tokenFromUser($spoofer);
        $this->withAuthorization('Bearer '.$token)->deleteJson($this->baseRoute.'/'.$post->post_id.'/bookmarks/'.$bookmark->bookmark_id);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testUpdateRateSpoof()
    {
        $user = $this->createUser();
        /** @var AbstractPost $post */
        $post = $this->getFactory($this->factoryClass)->create();
        $rating = $this->getFactory(App\Models\Rating::class)->make(
            [
                'user_id' => $user->user_id,
                'rating_value' => 5,
            ]);

        $post->userRatings()->save($rating);

        $this->assertEquals(5, $post->userRatings->first()->rating_value);
        $this->assertEquals(1, $post->userRatings->count());

        $spoofer = $this->createUser();
        $token = $this->tokenFromUser($spoofer);
        $this->withAuthorization('Bearer '.$token)->putJson($this->baseRoute.'/'.$post->post_id.'/ratings/'.$rating->rating_id, [
            'ratingId' => $rating->rating_id,
            'ratingValue' => 2,
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }
}
