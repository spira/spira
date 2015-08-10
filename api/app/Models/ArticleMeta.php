<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 03.08.15
 * Time: 13:29
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class ArticleMeta extends ChildBaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'article_metas';

    protected $primaryKey = 'meta_name';

    protected $fillable = ['article_id', 'meta_name', 'meta_content', 'meta_property'];

    public function getValidationRules()
    {
        $metaUniqueRule = 'unique:article_metas,meta_name';
        if ($this->exists) {
            $metaUniqueRule.=','.$this->meta_name.',meta_name';
        } else {
            $metaUniqueRule.=',NULL,NULL';
        }
        $metaUniqueRule.= ',article_id,'.$this->article_id;
        return [
            'article_id' => 'uuid|createOnly',
            'meta_name' => 'required|string|createOnly|'.$metaUniqueRule,
            'meta_content' => 'string',
            'meta_property' => 'string'
        ];
    }

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
