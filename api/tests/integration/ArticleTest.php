<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Article;
use App\Models\ArticleMeta;
use App\Models\ArticlePermalink;
use App\Models\Tag;
use App\Services\Api\Vanilla\Client as VanillaClient;

/**
 * Class ArticleTest.
 * @group integration
 */
class ArticleTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
        App\Models\Article::flushEventListeners();
        App\Models\Article::boot();
    }

    /**
     * @param Article[] $articles
     */
    protected function addPermalinksToArticles($articles)
    {
        foreach ($articles as $article) {
            $this->getFactory(ArticlePermalink::class)
                ->count(rand(2, 10))->make()->each(function (ArticlePermalink $permalink) use ($article) {
                    $article->articlePermalinks()->save($permalink);
                });
        }
    }

    /**
     * @param Article[] $articles
     */
    protected function addMetasToArticles($articles)
    {
        foreach ($articles as $article) {
            $uniqueMetas = [];
            $this->getFactory(ArticleMeta::class)
                ->count(4)
                ->make()
                ->each(function (ArticleMeta $meta) use ($article, &$uniqueMetas) {
                    if (! in_array($meta->meta_name, $uniqueMetas)) {
                        $article->articleMetas()->save($meta);
                        array_push($uniqueMetas, $meta->meta_name);
                    }
                });
        }
    }

    protected function cleanupDiscussions(array $articles)
    {
        foreach ($articles as $article) {
            // Deleting the article will trigger the deleted event that removes
            // the discussion
            $article->delete();
        }
    }

    public function testGetAllPaginated()
    {
        $entities = $this->getFactory(Article::class)->count(5)->create();

        $entity = $entities->first();
        $entity->excerpt = null;
        $this->addPermalinksToArticles($entities);

        $this->getJson('/articles', ['Range' => 'entities=0-19']);
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
        $object = json_decode($this->response->getContent());
        $this->assertNotNull($object[0]->excerpt);
        $this->assertObjectNotHasAttribute('content', $object[0]);

        $this->cleanupDiscussions($entities->all());
    }

    public function testGetOne()
    {
        $entity = $this->getFactory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);

        $this->getJson('/articles/'.$entity->article_id);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('articleId', $object);
        $this->assertObjectHasAttribute('authorId', $object);
        $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $object->articleId);
        $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $object->authorId);
        $this->assertTrue(strlen($object->articleId) === 36, 'UUID has 36 chars');

        $this->assertTrue(is_string($object->title));
        $this->assertTrue(is_string($object->permalink) || is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetOneWithNestedTags()
    {
        $entity = $this->getFactory(Article::class)->create();
        $tags = $this->getFactory(Tag::class)->count(4)->create();
        $entity->tags()->sync($tags->lists('tag_id')->toArray());

        $this->getJson('/articles/'.$entity->article_id, ['with-nested' => 'tags']);
        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('_tags', $object);
        $this->assertEquals(4, count($object->_tags));
    }

    public function testGetOneWithNestedAuthor()
    {
        $entity = $this->getFactory(Article::class)->create();

        $this->getJson('/articles/'.$entity->article_id, ['with-nested' => 'author']);
        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('_author', $object);
    }

    public function testGetOneByFirstPermalink()
    {
        $entity = $this->getFactory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);

        $permalink = $entity->articlePermalinks->first();
        $this->getJson('/articles/'.$permalink->permalink);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('articleId', $object);
        $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $object->articleId);
        $this->assertTrue(strlen($object->articleId) === 36, 'UUID has 36 chars');

        $this->assertTrue(is_string($object->title));
        $this->assertTrue(is_string($object->permalink) || is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetOneByLastPermalink()
    {
        $entity = $this->getFactory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);

        $permalink = $entity->articlePermalinks->last();
        $this->getJson('/articles/'.$permalink->permalink);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('articleId', $object);
        $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $object->articleId);
        $this->assertTrue(strlen($object->articleId) === 36, 'UUID has 36 chars');

        $this->assertTrue(is_string($object->title));
        $this->assertTrue(is_string($object->permalink) || is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testPostOne()
    {
        /** @var Article $entity */
        $entity = $this->getFactory(Article::class)
            ->setTransformer(\App\Http\Transformers\ArticleTransformer::class)
            ->transformed();

        $this->postJson('/articles', $entity);

        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);

        $this->cleanupDiscussions([Article::find($entity['articleId'])]);
    }

    public function testPutOneNew()
    {
        $entity = $this->getFactory(Article::class)
            ->setTransformer(\App\Http\Transformers\ArticleTransformer::class)
            ->transformed();

        $rowCount = Article::count();

        $this->putJson('/articles/'.$entity['articleId'], $entity);
        $this->shouldReturnJson();
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 1, Article::count());
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);

        $this->cleanupDiscussions([Article::find($entity['articleId'])]);
    }

    public function testPutOneNonExistingAuthor()
    {
        $entity = $this->getFactory(Article::class)
            ->customize(['author_id' => (string) \Rhumsaa\Uuid\Uuid::uuid4()])
            ->transformed();

        $this->putJson('/articles/'.$entity['articleId'], $entity);
        $this->shouldReturnJson();
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(422);
        $this->assertObjectHasAttribute('authorId', $object->invalid);

        $this->assertEquals('The selected author id is invalid.', $object->invalid->authorId[0]->message);
        $this->assertEquals('Exists', $object->invalid->authorId[0]->type);
    }

    public function testPutMissingIdInBody()
    {
        $factory = $this->getFactory(Article::class);
        $entity = $factory->create();
        $data = $factory->setTransformer(\App\Http\Transformers\ArticleTransformer::class)
            ->hide(['permalink','article_id'])
            ->customize(['title' => 'foo'])
            ->transformed();

        $this->putJson('/articles/'.$entity->article_id, $data);
        $this->shouldReturnJson();

        $this->assertResponseStatus(400);
    }

    public function testPatchOne()
    {
        $factory = $this->getFactory(Article::class);
        $entity = $factory->create();
        $data = $factory->setTransformer(\App\Http\Transformers\ArticleTransformer::class)
            ->hide(['permalink','article_id'])
            ->customize(['title' => 'foo'])
            ->transformed();

        $this->patchJson('/articles/'.$entity->article_id, $data);
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $checkEntity = Article::find($entity->article_id);
        $this->assertEquals($checkEntity->title, $entity->title);

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneNewPermalink()
    {
        $factory = $this->getFactory(Article::class);
        $entity = $factory->create();
        $data = $factory->setTransformer(\App\Http\Transformers\ArticleTransformer::class)
            ->hide(['article_id'])
            ->customize(['permalink' => 'foo_bar'])
            ->transformed();
        $this->addPermalinksToArticles([$entity]);

        $linksCount = $entity->articlePermalinks->count();

        $this->patchJson('/articles/'.$entity->article_id, $data);
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);

        $checkEntity = Article::find($entity->article_id);
        $this->assertEquals($checkEntity->permalink, $entity->permalink);
        $this->assertEquals($checkEntity->articlePermalinks->count(), $linksCount + 1);

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneRemovePermalink()
    {
        $factory = $this->getFactory(Article::class);
        $entity = $factory->create();
        $this->addPermalinksToArticles([$entity]);

        $linksCount = $entity->articlePermalinks->count();

        $data = $factory->setTransformer(\App\Http\Transformers\ArticleTransformer::class)
            ->customize(['permalink' => ''])
            ->transformed();

        $this->patchJson('/articles/'.$entity->article_id, $data);
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $checkEntity = Article::find($entity->article_id);
        $this->assertNull($checkEntity->permalink);
        $this->assertEquals($checkEntity->articlePermalinks->count(), $linksCount);

        $this->cleanupDiscussions([$entity]);
    }

    public function testDeleteOne()
    {
        $entities = $this->getFactory(Article::class)->count(5)->create()->all();
        $this->addPermalinksToArticles($entities);

        $entity = array_shift($entities);

        $entityPermalinksCount = $entity->articlePermalinks->count();
        $this->assertEquals($entityPermalinksCount, ArticlePermalink::where('article_id', '=', $entity->article_id)->count());

        $rowCount = Article::count();

        $permalinksTotalCount = ArticlePermalink::all()->count();
        $this->deleteJson('/articles/'.$entity->article_id);
        $permalinksTotalCountAfterDelete = ArticlePermalink::all()->count();

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, Article::count());
        $this->assertEquals($permalinksTotalCount - $entityPermalinksCount, $permalinksTotalCountAfterDelete);

        $this->cleanupDiscussions($entities);
    }

    public function testGetPermalinks()
    {
        $entity = $this->getFactory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);

        $count = ArticlePermalink::where('article_id', '=', $entity->article_id)->count();

        $this->getJson('/articles/'.$entity->article_id.'/permalinks');

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertEquals(count($object), $count);

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetPermalinksNotFoundArticle()
    {
        $this->getJson('/articles/foo_bar/permalinks');
        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
    }

    public function testGetMetas()
    {
        $entity = $this->getFactory(Article::class)->create();
        $this->addMetasToArticles([$entity]);

        $count = ArticleMeta::where('article_id', '=', $entity->article_id)->count();

        $this->getJson('/articles/'.$entity->article_id.'/meta');

        $articleCheck = Article::find($entity->article_id);
        $metaCheck = $articleCheck->articleMetas->first();
        $this->assertEquals($entity->article_id, $metaCheck->article->article_id);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertEquals(count($object), $count);

        $this->cleanupDiscussions([$entity]);
    }

    public function testPutMetas()
    {
        $article = $this->getFactory(Article::class)->create();
        $this->addMetasToArticles([$article]);

        $metaCount = ArticleMeta::where('article_id', '=', $article->article_id)->count();

        $entities = array_map(function (ArticleMeta $entity) {
            return $this->getFactory(ArticleMeta::class)->setModel($entity)->customize(['meta_content' => 'foobar'])->transformed();
        }, $article->articleMetas->all());

        $entities[] = $this->getFactory(ArticleMeta::class)->customize(
            [
                'meta_name' => 'barfoobar',
                'meta_content' => 'barfoobarfoo',
            ]
        )->transformed();

        $this->putJson('/articles/'.$article->article_id.'/meta', $entities);

        $this->assertResponseStatus(201);
        $updatedArticle = Article::find($article->article_id);

        $this->assertEquals($metaCount + 1, $updatedArticle->articleMetas->count());
        $counter = 0;
        foreach ($updatedArticle->articleMetas as $meta) {
            if ($meta->meta_content == 'foobar') {
                $counter++;
            }
        }
        $this->assertEquals($metaCount, $counter);

        $this->cleanupDiscussions([$article]);
    }

    public function testPutDuplicateMetaNames()
    {
        /** @var Article $article */
        $article = $this->getFactory(Article::class)->create();
        $factory = $this->getFactory(ArticleMeta::class)->customize(
            [
                'meta_name' => 'foo',
                'meta_content' => 'bar',
            ]
        );
        $meta = $factory->make();
        $article->articleMetas()->save($meta);
        $data = $factory->customize(
            [
                'meta_name' => 'foo',
                'meta_content' => 'foobar',
            ]
        )->transformed();

        $this->putJson('/articles/'.$article->article_id.'/meta', $data);

        $this->assertResponseStatus(500);
    }

    public function deleteMeta()
    {
        $article = $this->getFactory(Article::class)->create();
        $this->addMetasToArticles([$article]);

        $metaEntity = $article->articleMetas->first();
        $metaCount = ArticleMeta::where('article_id', '=', $article->article_id)->count();
        $this->deleteJson('/articles/'.$article->article_id.'/meta/'.$metaEntity->name);
        $updatedArticle = Article::find($article->article_id);
        $this->assertEquals($metaCount - 1, $updatedArticle->articleMetas->count());

        $this->cleanupDiscussions([$article]);
    }

    public function testShouldCreateDiscussionWhenArticleCreated()
    {
        $article = $this->getFactory(Article::class)->create();

        // Get the discussion
        $client = App::make(VanillaClient::class);
        $discussion = $client->api('discussions')->findByForeignId($article->article_id);

        $this->assertEquals($article->title, $discussion['Discussion']['Name']);
        $this->assertEquals($article->article_id, $discussion['Discussion']['ForeignID']);

        // Clean up by removing the discussion created
        $client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testShouldDeleteDiscussionWhenArticleDeleted()
    {
        $client = App::make(VanillaClient::class);

        $article = $this->getFactory(Article::class)->create();
        $discussion = $client->api('discussions')->findByForeignId($article->article_id);
        $article->delete();

        $this->assertEquals($article->article_id, $discussion['Discussion']['ForeignID']);
        $client->api('discussions')->findByForeignId($article->article_id);
    }

    public function testShouldGetCommentsForArticle()
    {
        $article = $this->getFactory(Article::class)->create();
        $body = 'A comment';

        // Get the discussion
        $client = App::make(VanillaClient::class);
        $discussion = $client->api('discussions')->findByForeignId($article->article_id);
        $discussionId = $discussion['Discussion']['DiscussionID'];

        // Add Comment
        $client->api('comments')->create($discussionId, $body);

        $this->getJson('/articles/'.$article->article_id.'/comments');
        $array = json_decode($this->response->getContent(), true);

        $this->assertCount(1, $array);
        $this->assertEquals($body, $array[0]['body']);

        // Clean up by removing the discussion created
        $client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    public function testShouldGetCommentsForArticleUsingWithNestedHeader()
    {
        $article = $this->getFactory(Article::class)->create();
        $body = 'A comment';

        // Get the discussion
        $client = App::make(VanillaClient::class);
        $discussion = $client->api('discussions')->findByForeignId($article->article_id);
        $discussionId = $discussion['Discussion']['DiscussionID'];

        // Add Comment
        $client->api('comments')->create($discussionId, $body);

        $this->getJson('/articles/'.$article->article_id, ['With-Nested' => 'comments']);
        $array = json_decode($this->response->getContent(), true);

        $this->assertCount(1, $array['_comments']);
        $this->assertEquals($body, $array['_comments'][0]['body']);

        // Clean up by removing the discussion created
        $client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    public function testShouldPostCommentForArticle()
    {
        $body = 'A comment';
        $article = $this->getFactory(Article::class)->create();

        $user = $this->createUser(['user_type' => 'guest']);
        $token = $this->tokenFromUser($user);

        $this->postJson('/articles/'.$article->article_id.'/comments', ['body' => $body], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
        $array = json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(200);
        $this->assertEquals($body, $array['body']);

        // Clean up Vanilla by removing the discussion and user created
        $client = App::make(VanillaClient::class);
        $client->api('discussions')->removeByForeignId($article->article_id);
        $user = $client->api('users')->sso($array['_author']['userId'], '', '');
        $client->api('users')->remove($user['User']['UserID']);
    }

    public function testShouldNotPostCommentWithoutBodyForArticle()
    {
        $article = $this->getFactory(Article::class)->create();

        $user = $this->createUser(['user_type' => 'guest']);
        $token = $this->tokenFromUser($user);

        $this->postJson('/articles/'.$article->article_id.'/comments', ['body' => ''], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $array = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('body', $array['invalid']);
        $this->assertResponseStatus(422);
    }

    public function testShouldNotPostCommentWithoutAuthedUserForArticle()
    {
        $body = 'A comment';
        $article = factory(Article::class)->create();

        $this->postJson('/articles/'.$article->article_id.'/comments', ['body' => $body]);

        $this->assertResponseStatus(401);
    }

    public function testShouldLogPutMetas()
    {
        $user = $this->createUser(['user_type' => 'guest']);
        $token = $this->tokenFromUser($user);
        $article = $this->getFactory(Article::class)->create();

        $meta = $this->getFactory(ArticleMeta::class)->make();
        $entities = [$meta];

        $this->putJson('/articles/'.$article->article_id.'/meta', $entities, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $article = Article::find($article->article_id);
        $this->assertCount(1, $revisions = $article->revisionHistory->toArray());
        $this->assertEquals($user->user_id, reset($revisions)['user_id']);

        $this->cleanupDiscussions([$article]);
    }

    public function testShouldLogDeleteMeta()
    {
        $article = $this->getFactory(Article::class)->create();
        $this->addMetasToArticles([$article]);

        $metaEntity = $article->articleMetas->first();
        $metaCount = $article->articleMetas->count();
        $this->deleteJson('/articles/'.$article->article_id.'/meta/'.$metaEntity->meta_id);

        $article = Article::find($article->article_id);

        $this->assertCount($metaCount + 1, $article->revisionHistory->toArray());

        $this->cleanupDiscussions([$article]);
    }

    public function testShouldCreateLocalisedArticle()
    {
        $article = factory(Article::class)->create();
        $locale = 'au';

        $data = [
            'title' => $title = 'localised title',
            'content' => $content = 'localised content',
        ];

        $article->fill($data);
        $article->save(['locale' => $locale]);

        $localised = DB::table('localizations')
            ->where('entity_id', $article->article_id)
            ->where('region_code', $locale)
            ->first();

        $localisations = json_decode($localised->localizations, true);

        $this->assertEquals($title, $localisations['title']);

        // Assert the cache
        $key = sprintf('l10n:%s:%s', $article->article_id, $locale);
        $cached = json_decode(Cache::get($key), true);
        $this->assertEquals($title, $cached['title']);

        $this->cleanupDiscussions([$article]);
    }

    public function testShouldUpdateLocalisedArticle()
    {
        $article = factory(Article::class)->create();
        $locale = 'au';

        $data = [
            'title' => 'localised title',
            'excerpt' => $excerpt = 'localised excerpt',
        ];

        $article->fill($data);
        $article->save(['locale' => $locale]);

        // Update the localised data
        $data = [
            'title' => $title = 'updated localised title',
        ];

        $article->fill($data);
        $article->save(['locale' => $locale]);

        $localised = DB::table('localizations')
            ->where('entity_id', $article->article_id)
            ->where('region_code', $locale)
            ->get();

        $localisations = json_decode(reset($localised)->localizations, true);

        $this->assertCount(1, $localised);
        $this->assertEquals($title, $localisations['title']);
        $this->assertEquals($excerpt, $localisations['excerpt']);

        // Assert the cache
        $key = sprintf('l10n:%s:%s', $article->article_id, $locale);
        $cached = json_decode(Cache::get($key), true);
        $this->assertEquals($title, $cached['title']);
        $this->assertEquals($excerpt, $cached['excerpt']);

        $this->cleanupDiscussions([$article]);
    }
}
