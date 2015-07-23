<?php namespace App\Models;

use Spira\Repository\Collection\Collection;

/**
 *
 * @property ArticlePermalink $permalinkRelation
 * @property ArticlePermalink[]|Collection $previousPermalinksRelations
 *
 * Class Article
 * @package App\Models
 *
 */
class Article extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'articles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['article_id', 'title', 'content', 'permalink', 'first_published'];

    protected $hidden = ['permalinkRelation','previousPermalinksRelations'];

    protected $primaryKey = 'article_id';

    protected $with = ['permalinkRelation'];

    protected $casts = [
        'first_published' => 'dateTime',
    ];

    /**
     * @param string $permalink
     */
    public function setPermalink($permalink)
    {
        $currentLink = $this->permalinkRelation;
        if ($currentLink) {
            $currentLink->current = false;
        }

        $permalinkObj = null;
        if ($permalink) {
            $permalinkObj = new ArticlePermalink();
            $permalinkObj->uri = $permalink;
            $permalinkObj->current = true;
        }
        $this->permalinkRelation = $permalinkObj;

        if ($currentLink && $currentLink->exists) {
            $this->previousPermalinksRelations->add($currentLink);
        }
    }

    public function getPermalink()
    {
        if ($this->permalinkRelation && $this->permalinkRelation->uri) {
            return $this->permalinkRelation->uri;
        }

        return null;
    }

    public function permalinkRelation()
    {
        $relation = $this->hasOne(ArticlePermalink::class, 'article_id', 'article_id');
        $relation->getQuery()->where('current', '=', 't');
        return $relation;
    }

    public function previousPermalinksRelations()
    {
        $relation = $this->hasMany(ArticlePermalink::class, 'article_id', 'article_id');
        $relation->getQuery()->where('current', '=', 'f');
        return $relation;
    }
}
