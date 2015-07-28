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
        'permalink' => 'string|unique:article_permalinks,permalink',
    ];


    private function savePermalink($permalinkSlug)
    {
        if ($permalinkSlug) {
            $permalink = new ArticlePermalink();
            $permalink->permalink = $permalinkSlug;
            $permalink->article()->associate($this);
            $permalink->save();

            $this->currentPermalink()->associate($permalink); //set permalink as this

        } else {
            $this->currentPermalink()->dissociate();
        }
        return $this;
    }


    public function currentPermalink()
    {
        return $this->belongsTo(ArticlePermalink::class, 'permalink');
    }

    public function permalinks()
    {
        return $this->hasMany(ArticlePermalink::class, 'article_id', 'article_id');
    }
}
