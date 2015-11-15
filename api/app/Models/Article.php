<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Rhumsaa\Uuid\Uuid;
use Illuminate\Support\Str;
use App\Models\Traits\TagTrait;
use Spira\Bookmark\Model\BookmarkableTrait;
use Spira\Model\Model\IndexedModel;
use Spira\Model\Collection\Collection;
use Spira\Model\Model\LocalizableModelTrait;
use Spira\Rate\Model\RateableTrait;
use Venturecraft\Revisionable\RevisionableTrait;
use Spira\Model\Model\LocalizableModelInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @property ArticlePermalink[]|Collection $permalinks
 * @property string $permalink
 *
 * Class Article
 */
class Article extends IndexedModel implements LocalizableModelInterface
{
    use RevisionableTrait, LocalizableModelTrait, TagTrait, RateableTrait, BookmarkableTrait;

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
    protected $fillable = [
        'article_id',
        'title',
        'excerpt',
        'permalink',
        'author_id',
        'author_display',
        'show_author_promo',
        'first_published',
        'sections_display',
        'primaryImage',
        'status',
    ];

    protected $hidden = ['permalinks'];

    protected $primaryKey = 'article_id';

    protected $casts = [
        'first_published' => 'datetime',
        self::CREATED_AT => 'datetime',
        self::UPDATED_AT => 'datetime',
        'sections_display' => 'json',
    ];

    protected $indexRelations = ['tags', 'articlePermalinks', 'author', 'articleMetas'];

    public static function getValidationRules($entityId = null)
    {
        return [
            'article_id' => 'required|uuid',
            'title' => 'required|string',
            'excerpt' => 'string',
            'primaryImage' => 'string',
            'status' => 'in:'.implode(',', static::$statuses),
            'permalink' => 'string|unique:article_permalinks,permalink,'.$entityId.',article_id',
            'sections_display' => 'decoded_json',
            'author_id' => 'required|uuid|exists:users,user_id',
        ];
    }

    // https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-core-types.html
    protected $mappingProperties = [
        'article_id' => [
            'type' => 'string',
            'index' => 'no', // also sets 'include_in_all' = false
        ],
        'primary_image' => [
            'type' => 'string',
            'index' => 'no',
        ],
        'permalink' => [
            'type' => 'string',
            'index' => 'no',
        ],
        'author_id' => [
            'type' => 'string',
            'include_in_all' => false, // This is filtered by exact ID, do not include in _all
        ],
        'sections_display' => [
            'type' => 'object',
            'index' => 'no',
        ],
        'excerpt' => [
            'type' => 'string',
            'index_analyzer' => 'autocomplete',
            'search_analyzer' => 'standard',
        ],
        'title' => [
            'type' => 'string',
            'index_analyzer' => 'autocomplete',
            'search_analyzer' => 'standard',
        ],
        'tags' => [
            'type' => 'nested',
            'include_in_all' => false,
        ],
        'article_permalinks' => [
            'type' => 'nested',
            'include_in_all' => false,
        ],
        'author' => [
            'type' => 'nested',
            'include_in_all' => false,
        ],
        'article_metas' => [
            'type' => 'nested',
            'include_in_all' => false,
        ],
    ];

    /**
     * Bootstrap model with event listeners.
     *
     * Saving permalink to history
     */
    public static function boot()
    {
        parent::boot();
        static::saving(function (Article $model) {
            if ($model->getOriginal('permalink') !== $model->permalink && ! is_null($model->permalink)) {
                $articlePermalink = new ArticlePermalink(['permalink' => $model->permalink]);
                $articlePermalink->save();
            }

            return true;
        });

        static::saved(function (Article $model) {
            if ($model->getOriginal('permalink') !== $model->permalink && ! is_null($model->permalink)) {
                $articlePermalink = ArticlePermalink::findOrFail($model->permalink);
                $model->articlePermalinks()->save($articlePermalink);
            }

            return true;
        });

        static::created(function (Article $article) {
            (new ArticleDiscussion)
                ->setArticle($article)
                ->createDiscussion();

            return true;
        });

        static::deleted(function (Article $article) {
            (new ArticleDiscussion)
                ->setArticle($article)
                ->deleteDiscussion();

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
     * If there is no defined excerpt for the text, create it from the content.
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

    public function articlePermalinks()
    {
        return $this->hasMany(ArticlePermalink::class, 'article_id', 'article_id');
    }

    public function articleMetas()
    {
        return $this->hasManyRevisionable(ArticleMeta::class, 'article_id', 'article_id', 'articleMetas');
    }

    /**
     * Get comment relationship.
     *
     * @return ArticleDiscussion
     */
    public function comments()
    {
        return (new ArticleDiscussion)->setArticle($this);
    }

    /**
     * @return HasMany
     */
    public function articleImages()
    {
        return $this->hasMany(ArticleImage::class);
    }

    public function author()
    {
        return $this->hasOne(User::class, 'user_id', 'author_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function sections()
    {
        return $this->morphMany(Section::class, 'sectionable');
    }
}
