<?php
use App\Models\Article;
use App\Models\ArticlePermalink;
use App\Repositories\ArticleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 24.07.15
 * Time: 0:18
 */


class ArticleRepositoryTest extends TestCase
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
        Article::flushEventListeners();
        Article::boot();

        // Get a repository instance, for assertions
        $this->repository = $this->app->make(ArticleRepository::class);
    }

    public function testFindByPermalinks()
    {
        $entities = $this->prepareArticlesWithPermalinks(rand(5, 15));
        $this->repository->saveMany($entities);

        /** @var Article $checkEntity */
        $checkEntity = end($entities);
        $checkUri = $checkEntity->permalink;
        $checkUriPrevious = $checkEntity->permalinks->last()->permalink;

        /** @var Article $model */
        $model = $this->repository->find($checkUri);

        $this->assertEquals($model->getQueueableId(), $checkEntity->getQueueableId());

        /** @var Article $model */
        $model = $this->repository->find($checkUriPrevious);

        $this->assertEquals($model->getQueueableId(), $checkEntity->getQueueableId());
    }

    public function testFindById()
    {
        $entities = $this->prepareArticlesWithPermalinks(rand(5, 15));
        $this->repository->saveMany($entities);

        /** @var Article $checkEntity */
        $checkEntity = end($entities);
        $id = $checkEntity->article_id;
        /** @var Article $model */
        $model = $this->repository->find($id);
        $this->assertEquals($model->getQueueableId(), $checkEntity->getQueueableId());
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
        return $entities;
    }

    protected function getLinkName($number)
    {
        return 'link_n_'.$number;
    }
}
