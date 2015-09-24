<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Spira\Model\Model\BaseModel;

class ArticleContentPiece extends BaseModel
{
    public $table = 'article_content_piece';

    protected $primaryKey = 'article_content_piece_id';

    public $timestamps = true;

    const CONTENT_TYPE_RICH_TEXT = 'rich_text';
    const CONTENT_TYPE_IMAGE = 'image';
    const CONTENT_TYPE_BLOCKQUOTE = 'blockquote';

    public static $contentTypes = [
        self::CONTENT_TYPE_RICH_TEXT,
        self::CONTENT_TYPE_IMAGE,
        self::CONTENT_TYPE_BLOCKQUOTE,
    ];

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
        'content' => 'required|json',
        'type' => 'required|content_piece_type',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
