<?php

namespace App\Models;

use App;
use Illuminate\Support\Collection;
use App\Services\Api\Vanilla\Client as VanillaClient;

class ArticleComment extends BaseModel
{
    /**
     * Article discussion belongs to.
     *
     * @var Article
     */
    protected $article;

    /**
     * Vanilla API client.
     *
     * @var VanillaClient
     */
    protected $client;

    /**
     * Assign dependencies.
     *
     * @param  Article $article
     *
     * @return void
     */
    public function __construct(Article $article)
    {
        $this->article = $article;

        $this->client = App::make(VanillaClient::class);
    }

    /**
     * Create a discussion thread for the article.
     *
     * @return void
     */
    public function newDiscussion()
    {
        $this->client->api('discussions')->create(
            $this->article->title,
            $this->article->excerpt,
            1,
            ['ForeignID' => $this->article->article_id]
        );
    }

    /**
     * Delete the discussion thread for the article.
     *
     * @return void
     */
    public function deleteDiscussion()
    {
        $this->client->api('discussions')->removeByForeignId(
            $this->article->article_id
        );
    }

    /**
     * Get the collection of comments.
     *
     * @return Collection
     */
    public function getResults()
    {
        // First a minimal call to the discussion for the total comment count
        $discussion = $this->client->api('discussions')->findByForeignId(
            $this->article->article_id,
            1,
            1
        );

        $commentCount = $discussion['Discussion']['CountComments'];

        // Now get the entire batch of comments
        $discussion = $this->client->api('discussions')->findByForeignId(
            $this->article->article_id,
            1,
            $commentCount
        );

        return new Collection($discussion['Comments']);
    }
}
