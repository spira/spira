<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Rhumsaa\Uuid\Uuid;
use Illuminate\Support\Str;
use App\Models\Traits\TagTrait;
use Spira\Model\Model\IndexedModel;
use App\Models\Traits\RateableTrait;
use App\Models\Traits\BookmarkableTrait;
use Spira\Model\Model\LocalizableModelTrait;
use Venturecraft\Revisionable\RevisionableTrait;
use Spira\Model\Model\LocalizableModelInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * model for AbstractPost.
 *
 * attributes
 *
 * @property string $post_id
 * @property string $title
 * @property string $status
 * @property string $excerpt
 * @property string $thumbnail_image_id
 * @property string $permalink
 * @property string $author_id
 * @property bool $author_display
 * @property bool $show_author_promo
 * @property \DateTime $first_published
 * @property string $sections_display json
 * @property string $post_type
 *
 * relations
 *
 * @property PostPermalink[]|Collection $permalinks
 * @property Meta[]|Collection $metas
 * @property Image $thumbnailImage
 * @property User $author
 * @property Section[]|Collection $sections
 * @property Tag[]|Collection $tags
 * @property Rating[]|Collection $userRatings
 * @property Bookmark[]|Collection $bookmarks
 */
class AbstractPost extends IndexedModel implements LocalizableModelInterface
{
    use RevisionableTrait, LocalizableModelTrait, TagTrait, RateableTrait, BookmarkableTrait;

    const defaultExcerptWordCount = 30;

    /**
     * Post statuses. ! WARNING these statuses define the enum types in the migration, don't remove any!
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_PUBLISHED = 'published';

    protected $tagTable = 'post_tag';
    protected $tagFk = 'post_id';

    public static $statuses = [self::STATUS_DRAFT, self::STATUS_SCHEDULED, self::STATUS_PUBLISHED];

    public static $postTypes = [
        Article::class,
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id',
        'title',
        'excerpt',
        'permalink',
        'author_id',
        'author_override',
        'author_website',
        'show_author_promo',
        'first_published',
        'sections_display',
        'thumbnail_image_id',
        'status',
        'users_can_comment',
        'public_access'
    ];

    protected $hidden = ['permalinks'];

    protected $primaryKey = 'post_id';

    protected $casts = [
        'first_published' => 'datetime',
        self::CREATED_AT => 'datetime',
        self::UPDATED_AT => 'datetime',
        'sections_display' => 'json',
    ];

    protected $indexRelations = ['tags', 'permalinks', 'author', 'metas'];

    public static function getValidationRules($entityId = null)
    {
        return [
            'post_id' => 'required|uuid',
            'title' => 'required|string',
            'excerpt' => 'string',
            'thumbnail_image_id' => 'uuid|exists:images,image_id',
            'status' => 'in:'.implode(',', static::$statuses),
            'permalink' => 'string|unique:permalink_post,permalink,'.$entityId.',post_id',
            'sections_display' => 'decoded_json',
            'author_id' => 'required|uuid|exists:users,user_id',
        ];
    }

    // https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-core-types.html
    protected $mappingProperties = [
        'post_id' => [
            'type' => 'string',
            'index' => 'no', // also sets 'include_in_all' = false
        ],
        'thumbnail_image_id' => [
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
        'permalinks' => [
            'type' => 'nested',
            'include_in_all' => false,
        ],
        'author' => [
            'type' => 'nested',
            'include_in_all' => false,
        ],
        'post_metas' => [
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
        static::bootScope();
        static::saving(function (AbstractPost $model) {
            if ($model->getOriginal('permalink') !== $model->permalink && ! is_null($model->permalink)) {
                $permalink = new PostPermalink(['permalink' => $model->permalink]);
                $permalink->save();
            }

            return true;
        });

        static::saved(function (AbstractPost $model) {
            if ($model->getOriginal('permalink') !== $model->permalink && ! is_null($model->permalink)) {
                $permalink = PostPermalink::findOrFail($model->permalink);
                $model->permalinks()->save($permalink);
            }

            return true;
        });

        static::created(function (AbstractPost $post) {
            (new PostDiscussion())
                ->setPost($post)
                ->createDiscussion();

            return true;
        });

        static::deleted(function (AbstractPost $post) {
            (new PostDiscussion())
                ->setPost($post)
                ->deleteDiscussion();

            return true;
        });
    }

    protected static function bootScope()
    {
        //static functions can not be abstract
        //anyway - this should be overridden
    }

    /**
     * @param string $id post_id or permalink
     * @return AbstractPost
     * @throws ModelNotFoundException
     */
    public function findByIdentifier($id)
    {
        //if the id is a uuid, try that or fail.
        if (Uuid::isValid($id)) {
            return static::findOrFail($id);
        }

        //otherwise attempt treat the id as a permalink and first try the model, then try the history
        try {
            return static::where('permalink', '=', $id)->firstOrFail();
        } catch (ModelNotFoundException $e) { //id or permalink not found, try permalink history
            $name = class_basename($this);

            return PostPermalink::findOrFail($id)->{$name};
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

    public function permalinks()
    {
        return $this->hasMany(PostPermalink::class, 'post_id', 'post_id');
    }

    public function metas()
    {
        return $this->morphMany(Meta::class, 'metaable');
    }

    /**
     * Get comment relationship.
     *
     * @return PostDiscussion
     */
    public function comments()
    {
        return (new PostDiscussion())->setPost($this);
    }

    public function thumbnailImage()
    {
        return $this->hasOne(Image::class, 'image_id', 'thumbnail_image_id');
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

    public function setPostTypeAttribute()
    {
        throw new \InvalidArgumentException('No direct attribute assignment');
    }
}
