<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 21.07.15
 * Time: 19:58
 */

namespace App\Models;

use Spira\Model\Model\BaseModel;

/**
 * @property string $permalink
 * @property Article article
 *
 * Class ArticlePermalink
 * @package App\Models
 *
 */
class ArticlePermalink extends BaseModel
{
    public $table = 'article_permalinks';

    protected $primaryKey = 'permalink';

    protected static $validationRules = [
        'permalink' => 'string|required',
        'check_entity_id' => 'uuid',
        'value' => 'required|string'
    ];

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'article_id');
    }
}
