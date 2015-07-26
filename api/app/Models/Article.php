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

    protected $hidden = ['pemalinks'];

    protected $primaryKey = 'article_id';

    protected $casts = [
        'first_published' => 'datetime',
    ];

    protected $validationRules = [
        'article_id' => 'uuid',
        'title' => 'required|string',
        'content' => 'required|string',
        'permalink' => 'string|unique:article_permalinks,permalink'
    ];

    /**
     * @param string $permalink
     */
    public function setPermalink($permalink)
    {
        $this->permalink = $permalink?:null;

        if ($permalink) {
            $permalinkObj = new ArticlePermalink();
            $permalinkObj->permalink = $permalink;

            $this->permalinks->add($permalinkObj);
        }
    }


    public function permalinks()
    {
        $relation = $this->hasMany(ArticlePermalink::class, 'article_id', 'article_id');
        return $relation;
    }
}
