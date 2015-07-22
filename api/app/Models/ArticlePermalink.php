<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 21.07.15
 * Time: 19:58
 */

namespace App\Models;


/**
 * @property bool $current
 * @property string $uri
 *
 * Class ArticlePermalink
 * @package App\Models
 *
 */
class ArticlePermalink extends BaseModel
{
    public $table = 'article_permalinks';

    protected $primaryKey = 'permalink_id';

    protected $validationRules = [
        'uri' => 'required|string|unique'
    ];
}