<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use App\Models\Sections\MediaContent;
use App\Models\Sections\PromoContent;
use App\Models\Sections\RichTextContent;
use App\Models\Sections\BlockquoteContent;
use Spira\Core\Model\Model\BaseModel;
use Spira\Core\Model\Model\LocalizableModelInterface;
use Spira\Core\Model\Model\LocalizableModelTrait;

class Section extends BaseModel implements LocalizableModelInterface
{
    use LocalizableModelTrait;

    public $table = 'sections';

    protected $primaryKey = 'section_id';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'section_id',
        'content',
        'type',
    ];

    public static $sectionableModels = [
        Article::class,
    ];

    protected static $validationRules = [
        'section_id' => 'required|uuid',
        'content' => 'required_if:type,'.RichTextContent::CONTENT_TYPE.','.BlockquoteContent::CONTENT_TYPE.','.MediaContent::CONTENT_TYPE,
        'type' => 'required|section_type',
    ];

    protected $casts = [
        self::CREATED_AT => 'datetime',
        self::UPDATED_AT => 'datetime',
        'content' => 'json',
    ];

    public static $contentTypeMap = [
        RichTextContent::CONTENT_TYPE => RichTextContent::class,
        BlockquoteContent::CONTENT_TYPE => BlockquoteContent::class,
        MediaContent::CONTENT_TYPE => MediaContent::class,
        PromoContent::CONTENT_TYPE => PromoContent::class,
    ];

    public static function getValidationRules($entityId = null)
    {
        return [
            'section_id' => 'required|uuid',
            'content' => 'required_if:type,'.RichTextContent::CONTENT_TYPE.','.BlockquoteContent::CONTENT_TYPE.','.MediaContent::CONTENT_TYPE,
            'type' => 'required|in:'.implode(',', static::getContentTypes()),
        ];
    }

    public static function getContentTypes()
    {
        return array_keys(self::$contentTypeMap);
    }
}
