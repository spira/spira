<?php

namespace App\Models;

use App;
use Spira\Model\Collection\Collection;
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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'comment_id',
        'content',
        'created_at',
        'author_name',
        'author_email',
        'author_photo'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Create a discussion thread for the article.
     *
     * @return void
     */
    public function newDiscussion()
    {
        $this->getClient()->api('discussions')->create(
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
        $this->getClient()->api('discussions')->removeByForeignId(
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
        $discussion = $this->getClient()->api('discussions')->findByForeignId(
            $this->article->article_id,
            1,
            1
        );

        $commentCount = $discussion['Discussion']['CountComments'];

        // Now get the entire batch of comments
        $discussion = $this->getClient()->api('discussions')->findByForeignId(
            $this->article->article_id,
            1,
            $commentCount
        );

        // Convert the comments to model objects
        $comments = new Collection;
        foreach ($discussion['Comments'] as $comment) {
            $comment = $this->vanillaCommentToEloquent($comment);

            $articleComment = new ArticleComment;
            $articleComment->fill($comment);

            $comments->push($articleComment);
        }

        return $comments;
    }

    /**
     * Convert a comment from Vanilla to be ready to fill an Eloquent model.
     *
     * @param  array  $data
     *
     * @return void
     */
    protected function vanillaCommentToEloquent(array $data)
    {
        $map = [
            'CommentID' => 'comment_id',
            'Body' => 'content',
            'DateInserted' => 'created_at',
            'InsertName' => 'author_name',
            'InsertEmail' => 'author_email',
            'InsertPhoto' => 'author_photo'
        ];

        $comment = [];

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $map)) {
                $comment[$map[$key]] = $value;
            }
        }

        return $comment;
    }

    /**
     * Sets the article the discussion belongs to.
     *
     * @param  Article $article
     *
     * @return ArticleComment
     */
    public function setArticle(Article $article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get Vanilla API client.
     *
     * @return VanillaClient
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = App::make(VanillaClient::class);
        }

        return $this->client;
    }
}
