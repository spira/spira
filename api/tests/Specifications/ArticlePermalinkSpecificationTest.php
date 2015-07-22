<?php

use App\Models\Article;
use App\Models\ArticlePermalink;
use App\Repositories\ArticleRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ArticlePermalinkSpecificationTest extends TestCase
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


    public function testIsSatisfiedBy()
    {
        $entities = $this->prepareArticlesWithPermalinks(rand(5,15));

        /** @var Article $checkEntity */
        $checkEntity = end($entities);
        $checkUri = $checkEntity->getPermalink();
        $checkUriPrevious = $checkEntity->previousPermalinksRelations->last()->uri;

        $specification = new \App\Specifications\ArticlePermalinkSpecification($checkUri);
        foreach ($entities as $entity)
        {
            if ($specification->isSatisfiedBy($entity)){
                $this->assertEquals($checkEntity,$entity);
            }
        }

        $specification = new \App\Specifications\ArticlePermalinkSpecification($checkUriPrevious);
        foreach ($entities as $entity)
        {
            if ($specification->isSatisfiedBy($entity)){
                $this->assertEquals($checkEntity,$entity);
            }
        }

    }

    public function testAttachCriteriaToBuilder()
    {
        $entities = $this->prepareArticlesWithPermalinks(rand(5,15));
        $this->repository->saveMany($entities);

        /** @var Article $checkEntity */
        $checkEntity = end($entities);
        $checkUri = $checkEntity->getPermalink();
        $checkUriPrevious = $checkEntity->previousPermalinksRelations->last()->uri;

        $specification = new \App\Specifications\ArticlePermalinkSpecification($checkUri);
        /** @var Article $model */
        $model = $this->repository->findSpecifying($specification)->first();

        $this->assertEquals($model->getQueueableId(),$checkEntity->getQueueableId());

        $specification = new \App\Specifications\ArticlePermalinkSpecification($checkUriPrevious);
        /** @var Article $model */
        $model = $this->repository->findSpecifying($specification)->first();

        $this->assertEquals($model->getQueueableId(),$checkEntity->getQueueableId());
    }

    public function testCompareResults()
    {
        $entities = $this->prepareArticlesWithPermalinks(rand(5,15));
        $this->repository->saveMany($entities);

        /** @var Article $checkEntity */
        $checkEntity = end($entities);
        $checkUri = $checkEntity->getPermalink();
        $checkUriPrevious = $checkEntity->previousPermalinksRelations->last()->uri;

        $specification = new \App\Specifications\ArticlePermalinkSpecification($checkUri);
        /** @var Article $model */
        $model = $this->repository->findSpecifying($specification)->first();
        foreach ($entities as $entity)
        {
            if ($specification->isSatisfiedBy($entity)){
                $this->assertEquals($entity->article_id,$model->article_id);
            }
        }

        $specification = new \App\Specifications\ArticlePermalinkSpecification($checkUriPrevious);
        /** @var Article $model */
        $model = $this->repository->findSpecifying($specification)->first();
        foreach ($entities as $entity)
        {
            if ($specification->isSatisfiedBy($entity)){
                $this->assertEquals($entity->article_id,$model->article_id);
            }
        }

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
        foreach ($entities as $entity)
        {
            $entity->setPermalink($this->getLinkName($counter++));
            $permalinks = [];

            for($i = rand(1,10); $i >= 0; $i--)
            {
                $permalinkObj = new ArticlePermalink();
                $permalinkObj->uri = $this->getLinkName($counter++);
                $permalinks[] = $permalinkObj;
            }

            $entity->previousPermalinksRelations = $permalinks;
        }
        return $entities;
    }

    protected function getLinkName($number)
    {
        return 'link_n_'.$number;
    }


}