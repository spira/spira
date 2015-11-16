<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Article;

class ArticleBookmarkRateTest extends TestCase
{
    public function testSimpleRate()
    {
        $user = $this->createUser();
        $article = $this->getFactory(Article::class)->create();
        $rateData = $this->getFactory(App\Models\Rating::class)->transformed();

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson('articles/'.$article->article_id.'/ratings/'.$rateData['ratingId'], $rateData);

        $this->assertResponseStatus(201);

        $this->assertEquals($user->ratedArticles->first()->article_id, $article->article_id);
        $rating = $user->ratedArticles->first()->pivot->rating_value;
        $this->assertTrue($rating > 0 && $rating < 6);
    }

    public function testInvalidRate()
    {
        $user = $this->createUser();
        $article = $this->getFactory(Article::class)->create();
        $rateData = $this->getFactory(App\Models\Rating::class)->customize(['rating_value' => 6])->transformed();

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson('articles/'.$article->article_id.'/ratings/'.$rateData['ratingId'], $rateData);

        $this->assertException('There was an issue with the validation of provided entity', 422, 'ValidationException');
    }

    public function testSimpleBookmark()
    {
        $user = $this->createUser();
        $article = $this->getFactory(Article::class)->create();
        $bookmarkData = $this->getFactory(App\Models\Bookmark::class)->transformed();

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson('articles/'.$article->article_id.'/bookmarks/'.$bookmarkData['bookmarkId'], $bookmarkData);

        $this->assertResponseStatus(201);

        $this->assertEquals($user->bookmarkedArticles->first()->article_id, $article->article_id);
    }

    public function testRemoveRate()
    {
        $user = $this->createUser();
        /** @var Article $article */
        $article = $this->getFactory(Article::class)->create();
        $rating = $this->getFactory(App\Models\Rating::class)->make(['user_id' => $user->user_id]);
        $article->userRatings()->save($rating);

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->deleteJson('articles/'.$article->article_id.'/ratings/'.$rating->rating_id);

        $this->assertResponseStatus(204);
        $this->assertEquals(0, $user->ratedArticles->count());
    }

    public function testRemoveBookmark()
    {
        $user = $this->createUser();
        /** @var Article $article */
        $article = $this->getFactory(Article::class)->create();
        $bookmark = $this->getFactory(App\Models\Bookmark::class)->make(['user_id' => $user->user_id]);
        $article->bookmarks()->save($bookmark);

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->deleteJson('articles/'.$article->article_id.'/bookmarks/'.$bookmark->bookmark_id);

        $this->assertResponseStatus(204);
        $this->assertEquals(0, $user->bookmarkedArticles->count());
    }

    public function testUpdateRate()
    {
        $user = $this->createUser();
        /** @var Article $article */
        $article = $this->getFactory(Article::class)->create();
        $rating = $this->getFactory(App\Models\Rating::class)->make(
            [
                'user_id' => $user->user_id,
                'rating_value' => 5,
            ]);

        $article->userRatings()->save($rating);

        $this->assertEquals(5, $article->userRatings->first()->rating_value);
        $this->assertEquals(1, $article->userRatings->count());

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson('articles/'.$article->article_id.'/ratings/'.$rating->rating_id, [
                'ratingId' => $rating->rating_id,
                'ratingValue' => 2,
        ]);

        $article = Article::find($article->article_id);

        $this->assertResponseStatus(201);
        $this->assertEquals(1, $article->userRatings->count());
        $this->assertEquals(2, $article->userRatings->first()->rating_value);
    }

    public function testRemoveRateSpoof()
    {
        $user = $this->createUser();
        /** @var Article $article */
        $article = $this->getFactory(Article::class)->create();
        $rating = $this->getFactory(App\Models\Rating::class)->make(['user_id' => $user->user_id]);
        $article->userRatings()->save($rating);

        $spoofer = $this->createUser();
        $token = $this->tokenFromUser($spoofer);
        $this->withAuthorization('Bearer '.$token)->deleteJson('articles/'.$article->article_id.'/ratings/'.$rating->rating_id);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testRemoveBookmarkSpoof()
    {
        $user = $this->createUser();
        /** @var Article $article */
        $article = $this->getFactory(Article::class)->create();
        $bookmark = $this->getFactory(App\Models\Bookmark::class)->make(['user_id' => $user->user_id]);
        $article->bookmarks()->save($bookmark);

        $spoofer = $this->createUser();
        $token = $this->tokenFromUser($spoofer);
        $this->withAuthorization('Bearer '.$token)->deleteJson('articles/'.$article->article_id.'/bookmarks/'.$bookmark->bookmark_id);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testUpdateRateSpoof()
    {
        $user = $this->createUser();
        /** @var Article $article */
        $article = $this->getFactory(Article::class)->create();
        $rating = $this->getFactory(App\Models\Rating::class)->make(
            [
                'user_id' => $user->user_id,
                'rating_value' => 5,
            ]);

        $article->userRatings()->save($rating);

        $this->assertEquals(5, $article->userRatings->first()->rating_value);
        $this->assertEquals(1, $article->userRatings->count());

        $spoofer = $this->createUser();
        $token = $this->tokenFromUser($spoofer);
        $this->withAuthorization('Bearer '.$token)->putJson('articles/'.$article->article_id.'/ratings/'.$rating->rating_id, [
            'ratingId' => $rating->rating_id,
            'ratingValue' => 2,
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }
}
