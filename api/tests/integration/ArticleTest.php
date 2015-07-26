<?php
use App\Models\Article;
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
        $transformer = $this->app->make(\App\Http\Transformers\IlluminateModelTransformer::class);
        $entity = $transformer->transform($entity);

        return $entity;
    }

    /**
     * @param $number
     * @return \App\Models\Article[]
     */
    protected function prepareArticlesWithPermalinks($number)
    {
        $counter = 1;
        /** @var Article[] $entities */
        $entities = factory(Article::class, $number)->create()->all();
        foreach ($entities as $entity) {
            $entity->permalink = $this->getLinkName($counter++);
            $permalinks = [];

            for ($i = rand(1, 10); $i >= 0; $i--) {
                $permalinkObj = new ArticlePermalink();
                $permalinkObj->permalink = $this->getLinkName($counter++);
                $permalinks[] = $permalinkObj;
            }

            $entity->permalinks = $permalinks;
        }
        $this->repository->saveMany($entities);
        return $entities;
    }

    protected function getLinkName($number)
    {
        return 'link_n_'.$number;
    }

    public function testGetAll()
    {
        $this->prepareArticlesWithPermalinks(5);
        $this->get('/articles');

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
    }

    public function testGetOne()
    {
        $entity = $this->prepareArticlesWithPermalinks(1)->first();

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
        $this->assertTrue(is_string($object->permalink));
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
        $this->assertEquals($checkEntity->title,$entity->title);
    }


    public function testPatchOne()
    {

    }

    public function testPatchOneNewPermalink()
    {

    }

    public function testPatchOneRemovePermalink()
    {

    }

    public function testDeleteOne()
    {
    }
}
