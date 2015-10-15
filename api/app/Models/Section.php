<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use App\Models\Sections\BlockquoteContent;
use App\Models\Sections\ImageContent;
use App\Models\Sections\PromoContent;
use App\Models\Sections\RichTextContent;
use Spira\Model\Model\BaseModel;

class Section extends BaseModel
{
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

    protected static $validationRules = [
        'section_id' => 'required|uuid',
        'content' => 'required_if:type,'.RichTextContent::CONTENT_TYPE.','.BlockquoteContent::CONTENT_TYPE,','.ImageContent::CONTENT_TYPE,
        'type' => 'required|section_type',
    ];

    protected $casts = [
        self::CREATED_AT => 'datetime',
        self::UPDATED_AT => 'datetime',
        'content' => 'json',
    ];

    public static function getContentTypes()
    {
        return [
            RichTextContent::CONTENT_TYPE,
            BlockquoteContent::CONTENT_TYPE,
            ImageContent::CONTENT_TYPE,
            PromoContent::CONTENT_TYPE,
        ];
    }
}
