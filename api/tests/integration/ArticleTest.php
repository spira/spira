<?php
use App\Models\Article;
use App\Models\ArticleMeta;
use App\Models\ArticlePermalink;
use App\Repositories\ArticleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ArticleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var ArticleRepository
     */
    private $repository;

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

        // Get a repository instance, for assertions
        $this->repository = $this->app->make(ArticleRepository::class);
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

    public function testGetAllPaginated()
    {
        $entities = factory(Article::class, 5)->create()->all();
        $entity = current($entities);
        $entity->excerpt = null;
        $this->addPermalinksToArticles($entities);
        $this->repository->saveMany($entities);
        $this->get('/articles', ['Range'=>'entities=0-19']);
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
        $object = json_decode($this->response->getContent());
        $this->assertNotNull($object[0]->excerpt);
        $this->assertObjectNotHasAttribute('content', $object[0]);
    }

    public function testGetOne()
    {
        $entities = factory(Article::class, 2)->create()->all();
        $this->addPermalinksToArticles($entities);
        $this->repository->saveMany($entities);
        $entity = current($entities);

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
    }

    public function testGetOneByPermalink()
    {
        $entities = factory(Article::class, 2)->create()->all();
        $this->addPermalinksToArticles($entities);
        $this->repository->saveMany($entities);
        $entity = current($entities);

        $this->get('/articles/'.$entity->permalink);

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
    }

    public function testPutOneNew()
    {
        $entity = factory(Article::class)->make();
        $id = $entity->article_id;

        $rowCount = $this->repository->count();

        $this->put('/articles/'.$id, $this->prepareEntity($entity));
        $this->shouldReturnJson();
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 1, $this->repository->count());
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);
    }

    public function testPutCollidingIds()
    {
        $entity = factory(Article::class)->create();
        $id = $entity->article_id;
        $entity->title = 'foo';

        $rowCount = $this->repository->count();

        $this->put('/articles/'.$id, $this->prepareEntity($entity));
        $this->shouldReturnJson();
        $object = json_decode($this->response->getContent());
        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount, $this->repository->count());
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);

        $checkEntity = $this->repository->find($id);
        $this->assertEquals($checkEntity->title, $entity->title);
    }


    public function testPatchOne()
    {
        $entity = factory(Article::class)->create();
        $id = $entity->article_id;

        $entity->title = 'foo';

        $this->patch('/articles/'.$id, $this->prepareEntity($entity));
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $checkEntity = $this->repository->find($id);
        $this->assertEquals($checkEntity->title, $entity->title);
    }

    public function testPatchOneNewPermalink()
    {
        $entities = factory(Article::class, 2)->create()->all();
        $this->addPermalinksToArticles($entities);
        $this->repository->saveMany($entities);
        $entity = current($entities);

        $id = $entity->article_id;
        $linksCount = $entity->permalinks->count();
        $entity->permalink = 'foo_bar';

        $this->patch('/articles/'.$id, $this->prepareEntity($entity));
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);

        $checkEntity = $this->repository->find($id);
        $this->assertEquals($checkEntity->permalink, $entity->permalink);
        $this->assertEquals($checkEntity->permalinks->count(), $linksCount+1);
    }

    public function testPatchOneRemovePermalink()
    {
        $entities = factory(Article::class, 2)->create()->all();
        $this->addPermalinksToArticles($entities);
        $this->repository->saveMany($entities);
        $entity = current($entities);

        $id = $entity->article_id;
        $linksCount = $entity->permalinks->count();

        $entity->permalink = '';

        $this->patch('/articles/'.$id, $this->prepareEntity($entity));
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $checkEntity = $this->repository->find($id);
        $this->assertNull($checkEntity->permalink);
        $this->assertEquals($checkEntity->permalinks->count(), $linksCount);
    }

    public function testDeleteOne()
    {
        $entities = factory(Article::class, 4)->create()->all();
        $this->addPermalinksToArticles($entities);
        $this->repository->saveMany($entities);

        $entity = $entities[0];
        $id = $entity->article_id;

        $entityPermalinksCount = $entity->permalinks->count();
        $this->assertEquals($entityPermalinksCount, ArticlePermalink::where('article_id', '=', $id)->count());

        $rowCount = $this->repository->count();

        $permalinksTotalCount = ArticlePermalink::all()->count();
        $this->delete('/articles/'.$id);
        $permalinksTotalCountAfterDelete = ArticlePermalink::all()->count();

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, $this->repository->count());
        $this->assertEquals($permalinksTotalCount - $entityPermalinksCount, $permalinksTotalCountAfterDelete);
    }

    public function testGetPermalinks()
    {
        $entity = factory(Article::class)->create();

        $count = ArticlePermalink::where('article_id', '=', $entity->article_id)->count();

        $this->get('/articles/'.$entity->article_id.'/permalinks');

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertEquals(count($object), $count);
    }

    public function testGetPermalinksNotFoundArticle()
    {
        $this->get('/articles/foo_bar/permalinks');
        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
    }

    public function testGetMetas()
    {
        $entities = factory(Article::class, 2)->create()->all();
        $this->addMetasToArticles($entities);
        $this->repository->saveMany($entities);
        $entity = current($entities);

        $count = ArticleMeta::where('article_id', '=', $entity->article_id)->count();

        $this->get('/articles/'.$entity->article_id.'/meta');

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertEquals(count($object), $count);
    }

}
