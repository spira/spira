<?php namespace App\Models;

use Spira\Repository\Collection\Collection;

/**
 *
 * @property ArticlePermalink[]|Collection $permalinks
 * @property string $permalink
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

    protected $hidden = ['permalinks'];

    protected $primaryKey = 'article_id';

    protected $casts = [
        'first_published' => 'datetime',
    ];

    protected $validationRules = [
        'article_id' => 'uuid',
        'title' => 'required|string',
        'content' => 'required|string',
//        'permalink' => 'string|unique:article_permalinks,permalink'
    ];

//    /**
//     * @param string $permalink
//     */
//    public function setPermalinkAttribute($permalink)
//    {
//        if ($permalink) {
//            $this->attributes['permalink'] = $permalink;
//            $permalinkObj = new ArticlePermalink();
//            $permalinkObj->permalink = $permalink;
//            $this->permalinks->add($permalinkObj);
//        } else {
//            $this->attributes['permalink'] = null;
//        }
//    }

    /**
     * @param string $permalinkSlug
     */
    public function setPermalinkAttribute($permalinkSlug)
    {
        if ($permalinkSlug) {
            $permalink = new ArticlePermalink();
            $permalink->permalink = $permalinkSlug;

            $this->permalinks()->save($permalink); //save to this model's permalink history

            $this->currentPermalink()->associate($permalink); //set permalink as this

        } else {
            $this->currentPermalink()->dissociate();
        }
    }


    public function currentPermalink()
    {
        return $this->belongsTo(ArticlePermalink::class, 'permalink');
    }

    public function permalinks()
    {
        return $this->hasMany(ArticlePermalink::class, 'permalink');
    }
}
