<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use App\Models\ArticleContent\BlockquoteContent;
use App\Models\ArticleContent\ImageContent;
use App\Models\ArticleContent\RichTextContent;
use Spira\Model\Model\BaseModel;

class ArticleContentPiece extends BaseModel
{
    public $table = 'article_content_pieces';

    protected $primaryKey = 'article_content_piece_id';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'article_content_piece_id',
        'article_id',
        'content',
        'type',
    ];

    protected static $validationRules = [
        'article_content_piece_id' => 'required|uuid',
        'article_id' => 'required|uuid',
        'content' => 'required|array',
        'type' => 'required|content_piece_type',
    ];

    public static function getContentTypes()
    {
        return [
            RichTextContent::CONTENT_TYPE,
            BlockquoteContent::CONTENT_TYPE,
            ImageContent::CONTENT_TYPE,
        ];
    }

    /**
     * Parse the json string
     * @param $content
     * @return mixed
     */
    public function getContentAttribute($content)
    {
        if (is_string($content)){
            return json_decode($content);
        }
        return $content;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
