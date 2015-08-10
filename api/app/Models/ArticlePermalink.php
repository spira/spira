<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 21.07.15
 * Time: 19:58
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;


/**
 * @property string $permalink
 * @property Article article
 *
 * Class ArticlePermalink
 * @package App\Models
 *
 */
class ArticlePermalink extends ChildBaseModel
{
    public $table = 'article_permalinks';

    protected $primaryKey = 'permalink';



    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'article_id');
    }



    /**
     * @param Builder $query
     * @param BaseModel $parent
     * @return Builder
     */
    protected function attachParentModelToQuery(Builder $query, BaseModel $parent)
    {
        $query->where('article_id','=',$parent->article_id);
        return $query;
    }


    /**
     * @param BaseModel $parent
     * @return void
     */
    public function attachParent(BaseModel $parent)
    {
        $this->article_id = $parent->article_id;
    }
}
