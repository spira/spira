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
        $rateData = $this->getFactory(\Spira\Rate\Model\Rating::class)->transformed();

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson('articles/'.$article->article_id.'/rate/'.$rateData['ratingId'], $rateData);

        $this->assertResponseStatus(201);

        $this->assertEquals($user->ratedArticles->first()->article_id, $article->article_id);
    }

    public function testSimpleBookmark()
    {
        $user = $this->createUser();
        $article = $this->getFactory(Article::class)->create();
        $bookmarkData = $this->getFactory(\Spira\Bookmark\Model\Bookmark::class)->transformed();

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson('articles/'.$article->article_id.'/bookmark/'.$bookmarkData['bookmarkId'], $bookmarkData);

        $this->assertResponseStatus(201);

        $this->assertEquals($user->bookmarkedArticles->first()->article_id, $article->article_id);
    }

    public function testRemoveRate()
    {
        $user = $this->createUser();
        /** @var Article $article */
        $article = $this->getFactory(Article::class)->create();
        $rating = $this->getFactory(\Spira\Rate\Model\Rating::class)->make(['user_id' => $user->user_id]);
        $article->rate()->save($rating);

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->deleteJson('articles/'.$article->article_id.'/rate/'.$rating->rating_id);

        $this->assertResponseStatus(204);
        $this->assertEquals(0, $user->ratedArticles->count());
    }

    public function testRemoveBookmark()
    {
        $user = $this->createUser();
        /** @var Article $article */
        $article = $this->getFactory(Article::class)->create();
        $bookmark = $this->getFactory(\Spira\Bookmark\Model\Bookmark::class)->make(['user_id' => $user->user_id]);
        $article->bookmark()->save($bookmark);

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->deleteJson('articles/'.$article->article_id.'/bookmark/'.$bookmark->bookmark_id);

        $this->assertResponseStatus(204);
        $this->assertEquals(0, $user->bookmarkedArticles->count());
    }

    public function testUpdateRate()
    {
        $user = $this->createUser();
        /** @var Article $article */
        $article = $this->getFactory(Article::class)->create();
        $rating = $this->getFactory(\Spira\Rate\Model\Rating::class)->make(
            [
                'user_id' => $user->user_id,
                'rating_value' => 5,
            ]);

        $article->rate()->save($rating);

        $this->assertEquals(5, $article->rate->first()->rating_value);
        $this->assertEquals(1, $article->rate->count());

        $token = $this->tokenFromUser($user);
        $this->withAuthorization('Bearer '.$token)->putJson('articles/'.$article->article_id.'/rate/'.$rating->rating_id, [
                'ratingId' => $rating->rating_id,
                'ratingValue' => 2,
        ]);

        $article = Article::find($article->article_id);

        $this->assertResponseStatus(201);
        $this->assertEquals(1, $article->rate->count());
        $this->assertEquals(2, $article->rate->first()->rating_value);
    }
}
