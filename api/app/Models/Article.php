<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Rhumsaa\Uuid\Uuid;
use Spira\Model\Collection\Collection;
use Spira\Model\Model\IndexedModel;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * @property ArticlePermalink[]|Collection $permalinks
 * @property ArticleMeta[]|Collection $metas
 * @property string $permalink
 *
 * Class Article
 */
class Article extends IndexedModel
{
    use RevisionableTrait;

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
    protected $fillable = ['article_id',
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

    protected $hidden = ['permalinks','metas'];

    protected $primaryKey = 'article_id';

    protected $casts = [
        'first_published' => 'datetime',
        self::CREATED_AT => 'datetime',
        self::UPDATED_AT => 'datetime',
        'sections_display' => 'json',
    ];

    protected $indexRelations = ['tags', 'articlePermalinks', 'author', 'articleMetas'];

    public static function getValidationRules()
    {
        return [
            'article_id' => 'required|uuid',
            'title' => 'required|string',
            'excerpt' => 'string',
            'primaryImage' => 'string',
            'status' => 'in:'.implode(',', static::$statuses),
            'permalink' => 'string|unique:article_permalinks,permalink',
            'sections_display' => 'array',
            'author_id' => 'required|uuid|exists:users,user_id',
        ];
    }

    protected $mappingProperties = [
        'tags' => [
            'type' => 'nested',
        ],
        'article_permalinks' => [
            'type' => 'nested',
        ],
        'author' => [
            'type' => 'nested',
        ],
        'article_metas' => [
            'type' => 'nested',
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
     * Parse the json string.
     * @param $content
     * @return mixed
     */
    public function getSectionsDisplayAttribute($content)
    {
        if (is_string($content)) {
            return json_decode($content);
        }

        return $content;
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

    public function tags()
    {
        return $this->belongsToManyRevisionable(Tag::class, 'tag_article', 'article_id', 'tag_id', 'tags');
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

    public function sections()
    {
        return $this->morphMany(Section::class, 'sectionable');
    }

    public function localizations()
    {
        return $this->morphMany(Localization::class, 'localizable');
    }
}
