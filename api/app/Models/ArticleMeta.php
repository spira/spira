<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 03.08.15
 * Time: 13:29
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

    protected $primaryKey = 'meta_name';

    protected $fillable = ['meta_name', 'meta_content', 'meta_property'];

    protected $guarded = ['meta_name'];

    public static function getValidationRules()
    {
        return [
            'meta_name' => 'required|string',
            'meta_content' => 'string',
            'meta_property' => 'string'
        ];
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'article_id');
    }
}
