<?php

namespace App\Models;

use App;
use Spira\Model\Model\BaseModel;
use Spira\Model\Collection\Collection;
use Spira\Model\Model\VirtualRelationInterface;
use App\Services\Api\Vanilla\Client as VanillaClient;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ArticleDiscussion extends BaseModel implements VirtualRelationInterface
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
     * Models to constrain with on parent collections.
     *
     * @var array
     */
    protected $eagerConstraints = [];

    /**
     * Get validation rules for a comment.
     *
     * @return array
     */
    public static function getValidationRules()
    {
        return ArticleComment::getValidationRules();
    }

    /**
     * Create a discussion thread for the article.
     *
     * @return void
     */
    public function createDiscussion()
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
        // Get a model for the comment
        $articleComment = (new ArticleComment)
            ->fill($options)
            ->setAuthor($user);

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
        $id = $this->getDiscussionId();
        $comment = $this->getClient()->api('comments')->create(
            $id,
            $articleComment->body
        );

        // Get ID from vanilla into model
        $articleComment->article_comment_id = $comment['Comment']['CommentID'];

        return $articleComment;
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
     * Get the collection of comments.
     *
     * @see \Illuminate\Database\Eloquent\Relations\HasMany::getResults()
     *
     * @return Collection
     */
    public function getResults()
    {
        // First a minimal call to the discussion for the total comment count
        $discussion = $this->getDiscussion($this->article->article_id, 1);
        $count = $discussion['Discussion']['CountComments'];

        // Now get the entire batch of comments
        $discussion = $this->getDiscussion($this->article->article_id, $count);

        // And turn them into a collection of models
        $comments = $this->prepareCommentsForHydrate($discussion['Comments']);
        $comments = (new ArticleComment)->hydrateRequestCollection($comments, new Collection);
        $comments = $this->setCommentAuthors($comments, $discussion['Comments']);

        return $comments;
    }

    /**
     * Get a discussion by querying Vanilla.
     *
     * @param  string $id
     * @param  int    $count
     *
     * @return array
     */
    protected function getDiscussion($id, $count)
    {
        return $this
            ->getClient()
            ->api('discussions')
            ->findByForeignId($id, 1, $count);
    }

    /**
     * Convert a comment from Vanilla to be ready to hydrate Eloquent model.
     *
     * @param  array  $comments
     *
     * @return array
     */
    protected function prepareCommentsForHydrate(array $comments = [])
    {
        $map = [
            'CommentID' => 'article_comment_id',
            'Body' => 'body',
            'DateInserted' => 'created_at',
        ];

        $comments = array_map(function ($comment) use ($map) {
            foreach ($comment as $key => $value) {
                if (array_key_exists($key, $map)) {
                    $comment[$map[$key]] = $value;
                }
            }

            return $comment;
        }, $comments);

        return $comments;
    }

    /**
     * Set authors for collection of comments.
     *
     * @param Collection $commentModels
     * @param array      $comments
     *
     * @return Collection
     */
    protected function setCommentAuthors(Collection $commentModels, array $comments)
    {
        foreach ($commentModels as $model) {
            $id = $model->article_comment_id;

            $comment = array_where($comments, function ($key, $value) use ($id) {
                return $value['CommentID'] == $id;
            });

            $email = reset($comment)['InsertEmail'];

            try {
                $user = (new User)->findByEmail($email);
            } catch (ModelNotFoundException $e) {
                $user = new User;
            }

            $model->setAuthor($user);
        }

        return $commentModels;
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @see \Illuminate\Database\Eloquent\Relations\HasOneOrMany::addEagerConstraints()
     *
     * @param  array  $models
     *
     * @return void
     */
    public function addEagerConstraints($models)
    {
        $this->eagerConstraints = $models;
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @see \Illuminate\Database\Eloquent\Relations\HasMany::initRelation()
     *
     * @param  array   $models
     * @param  string  $relation
     *
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->newCollection());
        }

        return $models;
    }

    /**
     * Get the relationship for eager loading.
     *
     * @see \Illuminate\Database\Eloquent\Relations\Relation::getEager()
     *
     * @return Collection
     */
    public function getEager()
    {
        $results = new Collection;

        foreach ($this->eagerConstraints as $model) {
            $comments = new ArticleDiscussion;
            $comments->setArticle($model);
            $results->offsetSet($model->getKey(), $comments->getResults());
        }

        return $results;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @see \Illuminate\Database\Eloquent\Relations\HasMany::match()
     *
     * @param  array       $models
     * @param  Collection  $results
     * @param  string      $relation
     *
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        foreach ($models as $model) {
            $key = $model->getKey();
            if ($results->offsetExists($key)) {
                $value = $results->offsetGet($key);

                $model->setRelation($relation, $value);
            }
        }

        return $models;
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
