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
     * Get validation rules.
     *
     * @return array
     */
    public static function getValidationRules()
    {
        return [
            'user_id' => 'required|uuid',
            'content' => 'required|string',
        ];
    }

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
     * Get the discussion thread id for the article.
     *
     * @return int
     */
    protected function getDiscussionId()
    {
        $discussion = $this->getClient()->api('discussions')->findByForeignId(
            $this->article->article_id
        );

        return $discussion['Discussion']['DiscussionID'];
    }

    /**
     * Save a new comment to Vanilla.
     *
     * @param  array     $options
     * @param  User|null $user
     *
     * @return ArticleComment
     */
    public function save(array $options = [], User $user = null)
    {
        $this->fill($options);
        $id = $this->getDiscussionId();

        // Get/create corresponding user from Vanilla
        $vanillaUser = $this->getClient()->api('users')->sso(
            $user->user_id,
            $user->username,
            $user->email,
            $user->avatar_image
        );

        // Set as active user in Vanilla client
        $this->getClient()->setUser($vanillaUser['User']['Name']);

        // Create the comment in Vanilla
        $comment = $this->getClient()->api('comments')->create(
            $id,
            $this->content
        );

        // And return it as an Eloquent Model
        $comment = $this->vanillaCommentToEloquent($comment['Comment']);
        $articleComment = new ArticleComment;
        $articleComment->fill($comment);

        return $articleComment;
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
     * Allow a parent model to get this model via relation.
     *
     * @return ArticleComment
     */
    public function getRelated()
    {
        return $this;
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
