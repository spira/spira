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

class ArticleMeta extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'article_metas';

    protected $primaryKey = 'meta_id';

    protected $fillable = ['meta_id', 'article_id', 'meta_name', 'meta_content'];

    protected $guarded = ['meta_name'];

    public static function getValidationRules()
    {
        return [
            'meta_name' => 'required|string',
            'meta_content' => 'string',
        ];
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'article_id');
    }
}
