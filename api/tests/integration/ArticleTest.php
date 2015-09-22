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
     * Prepare a factory generated entity to be sent as input data.
     *
     * @param Arrayable $entity
     *
     * @return array
     */
    protected function prepareEntity($entity)
    {
        // We run the entity through the transformer to get the attributes named
        // as if they came from the frontend.
        $transformer = $this->app->make(\App\Http\Transformers\EloquentModelTransformer::class);
        $entity = $transformer->transform($entity);

        return $entity;
    }

    protected function addPermalinksToArticles($articles)
    {
        foreach ($articles as $article) {
            $permalinks = factory(ArticlePermalink::class, rand(2, 10))->make()->all();
            foreach ($permalinks as $permalink) {
                $article->articlePermalinks->add($permalink);
            }
        }
    }

    protected function addMetasToArticles($articles)
    {
        foreach ($articles as $article) {
            $metas = factory(\App\Models\ArticleMeta::class, 4)->make()->all();
            $uniqueMetas = [];
            foreach ($metas as $meta) {
                if (! in_array($meta->meta_name, $uniqueMetas)) {
                    $article->articleMetas->add($meta);
                    array_push($uniqueMetas, $meta->meta_name);
                }
            }
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
        $entities = factory(Article::class, 5)->create()->all();
        $entity = current($entities);
        $entity->excerpt = null;
        $this->addPermalinksToArticles($entities);
        foreach ($entities as $oneEntity) {
            $oneEntity->push();
        }

        $this->getJson('/articles', ['Range' => 'entities=0-19']);
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
        $object = json_decode($this->response->getContent());
        $this->assertNotNull($object[0]->excerpt);
        $this->assertObjectNotHasAttribute('content', $object[0]);

        $this->cleanupDiscussions($entities);
    }

    public function testGetOne()
    {
        $entity = factory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);
        $entity->push();

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
        $this->assertTrue(is_string($object->content));
        $this->assertTrue(is_string($object->permalink) || is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetOneWithNestedTags()
    {
        $entity = factory(Article::class)->create();
        $tags = factory(\App\Models\Tag::class, 4)->create();
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
        $entity = $this->getFactory()->get(Article::class)->create();

        $this->getJson('/articles/'.$entity->article_id, ['with-nested' => 'author']);
        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('_author', $object);
    }

    public function testGetOneByFirstPermalink()
    {
        $entity = factory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);
        $entity->push();

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
        $this->assertTrue(is_string($object->content));
        $this->assertTrue(is_string($object->permalink) || is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetOneByLastPermalink()
    {
        $entity = factory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);
        $entity->push();

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
        $this->assertTrue(is_string($object->content));
        $this->assertTrue(is_string($object->permalink) || is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testPostOne()
    {
        /** @var Article $entity */
        $entity = factory(Article::class)->make();

        $this->postJson('/articles', $this->prepareEntity($entity));

        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);

        $this->cleanupDiscussions([Article::find($entity->article_id)]);
    }

    public function testPutOneNew()
    {
        $entity = factory(Article::class)->make();
        $id = $entity->article_id;

        $rowCount = Article::count();

        $requestData = $this->prepareEntity($entity);
        $this->putJson('/articles/'.$id, $requestData);
        $this->shouldReturnJson();
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 1, Article::count());
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);

        $this->cleanupDiscussions([Article::find($entity->article_id)]);
    }

    public function testPutOneNonExistingAuthor()
    {
        $entity = $this->getFactory()->get(Article::class)
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
        $entity = factory(Article::class)->create();
        $id = $entity->article_id;
        $entity->title = 'foo';

        $preparedEntity = $this->prepareEntity($entity);
        unset($preparedEntity['permalink'], $preparedEntity['articleId']);

        $this->putJson('/articles/'.$id, $preparedEntity);
        $this->shouldReturnJson();

        $this->assertResponseStatus(400);
    }

    public function testPatchOne()
    {
        $entity = factory(Article::class)->create();
        $id = $entity->article_id;
        $entity->title = 'foo';
        $preparedEntity = $this->prepareEntity($entity);
        unset($preparedEntity['permalink'], $preparedEntity['articleId']);
        $this->patchJson('/articles/'.$id, $preparedEntity);
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $checkEntity = Article::find($id);
        $this->assertEquals($checkEntity->title, $entity->title);

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneNewPermalink()
    {
        $entity = factory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);
        $entity->push();

        $id = $entity->article_id;
        $linksCount = $entity->articlePermalinks->count();
        $entity->permalink = 'foo_bar';

        $preparedEntity = $this->prepareEntity($entity);
        unset($preparedEntity['articleId']);
        $this->patchJson('/articles/'.$id, $preparedEntity);
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);

        $checkEntity = Article::find($id);
        $this->assertEquals($checkEntity->permalink, $entity->permalink);
        $this->assertEquals($checkEntity->articlePermalinks->count(), $linksCount + 1);

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneRemovePermalink()
    {
        $entity = factory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);
        $entity->push();

        $id = $entity->article_id;
        $linksCount = $entity->articlePermalinks->count();

        $entity->permalink = '';

        $this->patchJson('/articles/'.$id, $this->prepareEntity($entity));
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $checkEntity = Article::find($id);
        $this->assertNull($checkEntity->permalink);
        $this->assertEquals($checkEntity->articlePermalinks->count(), $linksCount);

        $this->cleanupDiscussions([$entity]);
    }

    public function testDeleteOne()
    {
        $entities = factory(Article::class, 4)->create()->all();
        $this->addPermalinksToArticles($entities);
        foreach ($entities as $oneEntity) {
            $oneEntity->push();
        }

        $entity = array_shift($entities);
        $id = $entity->article_id;

        $entityPermalinksCount = $entity->articlePermalinks->count();
        $this->assertEquals($entityPermalinksCount, ArticlePermalink::where('article_id', '=', $id)->count());

        $rowCount = Article::count();

        $permalinksTotalCount = ArticlePermalink::all()->count();
        $this->deleteJson('/articles/'.$id);
        $permalinksTotalCountAfterDelete = ArticlePermalink::all()->count();

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, Article::count());
        $this->assertEquals($permalinksTotalCount - $entityPermalinksCount, $permalinksTotalCountAfterDelete);

        $this->cleanupDiscussions($entities);
    }

    public function testGetPermalinks()
    {
        $entity = factory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);
        $entity->push();

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
        $entity = factory(Article::class)->create();
        $this->addMetasToArticles([$entity]);
        $entity->push();

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
        $article = factory(Article::class)->create();
        $this->addMetasToArticles([$article]);
        $article->push();

        $metaCount = ArticleMeta::where('article_id', '=', $article->article_id)->count();

        $entities = array_map(function ($entity) {
            return array_add($this->prepareEntity($entity), 'meta_content', 'foobar');
        }, $article->articleMetas->all());

        $meta = factory(\App\Models\ArticleMeta::class)->make([
            'meta_name' => 'barfoobar',
            'meta_content' => 'barfoobarfoo',
        ]);
        $entities[] = $this->prepareEntity($meta);

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
        $this->assertEquals($counter, $metaCount);

        $this->cleanupDiscussions([$article]);
    }

    public function testPutDuplicateMetaNames()
    {
        $article = factory(Article::class)->create();
        $article->articleMetas->add(factory(\App\Models\ArticleMeta::class)->make([
            'meta_name' => 'foo',
            'meta_content' => 'bar',
        ]));
        $article->push();

        $this->putJson('/articles/'.$article->article_id.'/meta', $this->prepareEntity(
            factory(\App\Models\ArticleMeta::class)->make([
                'meta_name' => 'foo',
                'meta_content' => 'foobar',
            ])
        ));

        $this->assertResponseStatus(500);
    }

    public function deleteMeta()
    {
        $articles = factory(Article::class, 2)->create()->all();
        $this->addMetasToArticles($articles);
        foreach ($articles as $oneEntity) {
            $oneEntity->push();
        }
        $article = current($articles);
        $metaEntity = $article->articleMetas->first();
        $metaCount = ArticleMeta::where('article_id', '=', $article->article_id)->count();
        $this->deleteJson('/articles/'.$article->article_id.'/meta/'.$metaEntity->name);
        $updatedArticle = Article::find($article->article_id);
        $this->assertEquals($metaCount - 1, $updatedArticle->articleMetas->count());

        $this->cleanupDiscussions($articles);
    }

    public function testShouldCreateDiscussionWhenArticleCreated()
    {
        $article = factory(Article::class)->create();

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

        $article = factory(Article::class)->create();
        $discussion = $client->api('discussions')->findByForeignId($article->article_id);
        $article->delete();

        $this->assertEquals($article->article_id, $discussion['Discussion']['ForeignID']);
        $client->api('discussions')->findByForeignId($article->article_id);
    }

    public function testShouldGetCommentsForArticle()
    {
        $article = factory(Article::class)->create();
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
        $article = factory(Article::class)->create();
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
        $article = factory(Article::class)->create();

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
        $article = factory(Article::class)->create();

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
        $article = factory(Article::class)->create();

        $meta = factory(\App\Models\ArticleMeta::class)->make();
        $entities = [];
        array_push($entities, $this->prepareEntity($meta));

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
        $article = factory(Article::class)->create();
        $this->addMetasToArticles([$article]);
        $article->push();

        $metaEntity = $article->articleMetas->first();
        $this->deleteJson('/articles/'.$article->article_id.'/meta/'.$metaEntity->meta_id);

        $article = Article::find($article->article_id);

        $this->assertCount(1, $article->revisionHistory->toArray());

        $this->cleanupDiscussions([$article]);
    }
}
