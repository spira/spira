<?php
use App\Models\Article;
use App\Models\ArticlePermalink;
use App\Repositories\ArticleRepository;

/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 24.07.15
 * Time: 0:18
 */


class ArticleRepositoryTest extends TestCase
{
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
        Article::flushEventListeners();
        Article::boot();

        // Get a repository instance, for assertions
        $this->repository = $this->app->make(ArticleRepository::class);
    }

    public function testFindByPermalinks()
    {
        $entities = factory(Article::class, rand(5, 15))->create()->all();
        $this->addPermalinksToArticles($entities);
        $this->repository->saveMany($entities);

        /** @var Article $checkEntity */
        $checkEntity = end($entities);
        $checkUri = $checkEntity->permalink;
        $checkUriPrevious = $checkEntity->permalinks->first()->permalink;

        /** @var Article $model */
        $model = $this->repository->find($checkUri);

        $this->assertEquals($model->getQueueableId(), $checkEntity->getQueueableId());

        /** @var Article $model */
        $model = $this->repository->find($checkUriPrevious);

        $this->assertEquals($model->getQueueableId(), $checkEntity->getQueueableId());
    }

    public function testFindById()
    {
        $entities = factory(Article::class, rand(5, 15))->create()->all();
        $this->addPermalinksToArticles($entities);
        $this->repository->saveMany($entities);

        /** @var Article $checkEntity */
        $checkEntity = end($entities);
        $id = $checkEntity->article_id;
        /** @var Article $model */
        $model = $this->repository->find($id);
        $this->assertEquals($model->getQueueableId(), $checkEntity->getQueueableId());
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
}
