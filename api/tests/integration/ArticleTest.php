<?php
use App\Models\Article;
use App\Models\ArticleMeta;
use App\Models\ArticlePermalink;
use App\Services\Api\Vanilla\Client as VanillaClient;

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
                $article->permalinks->add($permalink);
            }
        }
    }

    protected function addMetasToArticles($articles)
    {
        foreach ($articles as $article) {
            $metas = factory(\App\Models\ArticleMeta::class, 4)->make()->all();
            foreach ($metas as $meta) {
                $article->metas->add($meta);
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

        $this->get('/articles', ['Range'=>'entities=0-19']);
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

        $this->get('/articles/'.$entity->article_id);

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
        $this->assertTrue(is_string($object->permalink)||is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetOneWithNestedTags()
    {
        $entity = factory(Article::class)->create();
        $tags = factory(\App\Models\Tag::class, 4)->create();
        $entity->tags()->sync($tags->lists('tag_id')->toArray());

        $this->get('/articles/'.$entity->article_id, ['with-nested'=> 'tags']);
        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('_tags', $object);
        $this->assertEquals(4, count($object->_tags));
    }

    public function testGetOneByFirstPermalink()
    {
        $entity = factory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);
        $entity->push();

        $permalink = $entity->permalinks->first();
        $this->get('/articles/'.$permalink->permalink);

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
        $this->assertTrue(is_string($object->permalink)||is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetOneByLastPermalink()
    {
        $entity = factory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);
        $entity->push();

        $permalink = $entity->permalinks->last();
        $this->get('/articles/'.$permalink->permalink);

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
        $this->assertTrue(is_string($object->permalink)||is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }


    public function testPostOne()
    {
        $entity = factory(Article::class)->make();

        $this->post('/articles', $this->prepareEntity($entity));

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

        $this->put('/articles/'.$id, $this->prepareEntity($entity));
        $this->shouldReturnJson();
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 1, Article::count());
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);

        $this->cleanupDiscussions([Article::find($entity->article_id)]);
    }

    public function testPutMissingIdInBody()
    {
        $entity = factory(Article::class)->create();
        $id = $entity->article_id;
        $entity->title = 'foo';

        $preparedEntity = $this->prepareEntity($entity);
        unset($preparedEntity['permalink'], $preparedEntity['articleId']);

        $this->put('/articles/'.$id, $preparedEntity);
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
        $this->patch('/articles/'.$id, $preparedEntity);
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
        $linksCount = $entity->permalinks->count();
        $entity->permalink = 'foo_bar';

        $preparedEntity = $this->prepareEntity($entity);
        unset($preparedEntity['articleId']);
        $this->patch('/articles/'.$id, $preparedEntity);
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);

        $checkEntity = Article::find($id);
        $this->assertEquals($checkEntity->permalink, $entity->permalink);
        $this->assertEquals($checkEntity->permalinks->count(), $linksCount+1);

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneRemovePermalink()
    {
        $entity = factory(Article::class)->create();
        $this->addPermalinksToArticles([$entity]);
        $entity->push();

        $id = $entity->article_id;
        $linksCount = $entity->permalinks->count();

        $entity->permalink = '';

        $this->patch('/articles/'.$id, $this->prepareEntity($entity));
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $checkEntity = Article::find($id);
        $this->assertNull($checkEntity->permalink);
        $this->assertEquals($checkEntity->permalinks->count(), $linksCount);

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

        $entityPermalinksCount = $entity->permalinks->count();
        $this->assertEquals($entityPermalinksCount, ArticlePermalink::where('article_id', '=', $id)->count());

        $rowCount = Article::count();

        $permalinksTotalCount = ArticlePermalink::all()->count();
        $this->delete('/articles/'.$id);
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

        $this->get('/articles/'.$entity->article_id.'/permalinks');

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertEquals(count($object), $count);

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetPermalinksNotFoundArticle()
    {
        $this->get('/articles/foo_bar/permalinks');
        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
    }

    public function testGetMetas()
    {
        $entity = factory(Article::class)->create();
        $this->addMetasToArticles([$entity]);
        $entity->push();

        $count = ArticleMeta::where('article_id', '=', $entity->article_id)->count();

        $this->get('/articles/'.$entity->article_id.'/meta');

        $articleCheck = Article::find($entity->article_id);
        $metaCheck = $articleCheck->metas->first();
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
        }, $article->metas->all());

        $metas = factory(\App\Models\ArticleMeta::class, 2)->make()->all();
        foreach ($metas as $meta) {
            $entities[] = $this->prepareEntity($meta);
        }

        $this->put('/articles/'.$article->article_id.'/meta', ['data' => $entities]);

        $this->assertResponseStatus(201);
        $updatedArticle = Article::find($article->article_id);

        $this->assertEquals($metaCount+2, $updatedArticle->metas->count());
        $counter = 0;
        foreach ($updatedArticle->metas as $meta) {
            if ($meta->meta_content == 'foobar') {
                $counter++;
            }
        }
        $this->assertEquals($counter, $metaCount);

        $this->cleanupDiscussions([$article]);
    }

    public function deleteMeta()
    {
        $articles = factory(Article::class, 2)->create()->all();
        $this->addMetasToArticles($articles);
        foreach ($articles as $oneEntity) {
            $oneEntity->push();
        }
        $article = current($articles);
        $metaEntity = $article->metas->first();
        $metaCount = ArticleMeta::where('article_id', '=', $article->article_id)->count();
        $this->delete('/articles/'.$article->article_id.'/meta/'.$metaEntity->name);
        $updatedArticle = Article::find($article->article_id);
        $this->assertEquals($metaCount-1, $updatedArticle->metas->count());

        $this->cleanupDiscussions($articles);
    }

    /**
     * @test
     */
    public function shouldCreateDiscussionWhenArticleCreated()
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
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function shouldDeleteDiscussionWhenArticleDeleted()
    {
        $client = App::make(VanillaClient::class);

        $article = factory(Article::class)->create();
        $discussion = $client->api('discussions')->findByForeignId($article->article_id);
        $article->delete();

        $this->assertEquals($article->article_id, $discussion['Discussion']['ForeignID']);
        $client->api('discussions')->findByForeignId($article->article_id);
    }

    /**
     * @test
     */
    public function shouldGetCommentsForArticle()
    {
        $article = factory(Article::class)->create();
        $body = 'A comment';

        // Get the discussion
        $client = App::make(VanillaClient::class);
        $discussion = $client->api('discussions')->findByForeignId($article->article_id);
        $discussionId = $discussion['Discussion']['DiscussionID'];

        // Add Comment
        $client->api('comments')->create($discussionId, $body);

        $this->get('/articles/'.$article->article_id.'/comments');
        $array = json_decode($this->response->getContent(), true);

        $this->assertCount(1, $array);
        $this->assertEquals($body, $array[0]['body']);

        // Clean up by removing the discussion created
        $client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    /**
     * @test
     */
    public function shouldGetCommentsForArticleUsingWithNestedHeader()
    {
        $article = factory(Article::class)->create();
        $body = 'A comment';

        // Get the discussion
        $client = App::make(VanillaClient::class);
        $discussion = $client->api('discussions')->findByForeignId($article->article_id);
        $discussionId = $discussion['Discussion']['DiscussionID'];

        // Add Comment
        $client->api('comments')->create($discussionId, $body);

        $this->get('/articles/'.$article->article_id, ['With-Nested' => 'comments']);
        $array = json_decode($this->response->getContent(), true);

        $this->assertCount(1, $array['_comments']);
        $this->assertEquals($body, $array['_comments'][0]['body']);

        // Clean up by removing the discussion created
        $client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }
}
