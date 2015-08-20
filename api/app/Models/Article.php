<?php namespace App\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Rhumsaa\Uuid\Uuid;
use Spira\Model\Collection\Collection;

/**
 *
 * @property ArticlePermalink[]|Collection $permalinks
 * @property ArticleMeta[]|Collection $metas
 * @property string $permalink
 *
 * Class Article
 * @package App\Models
 *
 */
class Article extends BaseModel
{
    const defaultExcerptWordCount = 30;


    /**
     * Article statuses. ! WARNING these statuses define the enum types in the migration, don't remove any!
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_PUBLISHED = 'published';

    public static $statuses = [self::STATUS_DRAFT, self::STATUS_SCHEDULED, self::STATUS_PUBLISHED];

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
    protected $fillable = ['article_id', 'title', 'content', 'excerpt', 'permalink', 'first_published', 'primaryImage', 'status'];

    protected $hidden = ['permalinks','metas'];

    protected $primaryKey = 'article_id';

    protected $casts = [
        'first_published' => 'datetime',
    ];

    public static function getValidationRules()
    {
        return [
            'article_id' => 'required|uuid',
            'title' => 'required|string',
            'content' => 'required|string',
            'excerpt' => 'string',
            'primaryImage' => 'string',
            'status' => 'in:' . implode(',', static::$statuses),
            'permalink' => 'string|unique:article_permalinks,permalink'
        ];
    }

    /**
     * Bootstrap model with event listeners.
     *
     * Saving permalink to history
     */
    protected static function boot()
    {
        parent::boot();
        static::saving(function (Article $model) {
            if ($model->getOriginal('permalink') !== $model->permalink && !is_null($model->permalink)) {
                $articlePermalink = new ArticlePermalink();
                $articlePermalink->permalink = $model->permalink;
                $articlePermalink->save();
            }
            return true;
        });

        static::saved(function (Article $model) {
            if ($model->getOriginal('permalink') !== $model->permalink && !is_null($model->permalink)) {
                $articlePermalink = ArticlePermalink::findOrFail($model->permalink);
                $model->permalinks()->save($articlePermalink);
            }
            return true;
        });

        static::created(function (Article $article) {
            $articleComment = (new ArticleComment)->setArticle($article);
            $articleComment->newDiscussion();

            return true;
        });

        static::deleted(function (Article $article) {
            $articleComment = (new ArticleComment)->setArticle($article);
            $articleComment->deleteDiscussion();

            return true;
        });
    }

    /**
     * @param string $id article_id or permalink
     * @return Article
     * @throws ModelNotFoundException
     */
    public function findByIdentifier($id)
    {
        //if the id is a uuid, try that or fail.
        if (Uuid::isValid($id)) {
            return parent::findOrFail($id);
        }

        //otherwise attempt treat the id as a permalink and first try the model, then try the history
        try {
            return $this->where('permalink', '=', $id)->firstOrFail();
        } catch (ModelNotFoundException $e) { //id or permalink not found, try permalink history
            return ArticlePermalink::findOrFail($id)->article;
        }
    }

    public function setPermalinkAttribute($permalink)
    {
        if ($permalink) {
            $this->attributes['permalink'] = $permalink;
        } else {
            $this->attributes['permalink'] = null;
        }
    }

    /**
     * If there is no defined exerpt for the text, create it from the content
     * @param $excerpt
     * @return string
     */
    public function getExcerptAttribute($excerpt)
    {
        if ($excerpt) {
            return $excerpt; //if it is already set, do nothing
        }

        return Str::words($this->content, self::defaultExcerptWordCount, '');
    }

    public function permalinks()
    {
        return $this->hasMany(ArticlePermalink::class, 'article_id', 'article_id');
    }

    public function metas()
    {
        return $this->hasMany(ArticleMeta::class, 'article_id', 'article_id');
    }

    /**
     * Get comment relationship.
     *
     * @return ArticleComment
     */
    public function comments()
    {
        return (new ArticleComment)->setArticle($this);
    }
}
