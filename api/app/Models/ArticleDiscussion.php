<?php

namespace App\Models;

use App;
use App\Services\Api\Vanilla\Client as VanillaClient;

class ArticleDiscussion extends BaseModel
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
}
