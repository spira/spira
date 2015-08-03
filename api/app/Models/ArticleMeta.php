<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 03.08.15
 * Time: 13:29
 */

namespace App\Models;


class ArticleMeta extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'article_metas';

    protected $primaryKey = 'article_meta_name';

    protected $fillable = ['article_id', 'article_meta_name', 'content', 'property'];

    protected $validationRules = [
        'article_id' => 'uuid|createOnly',
        'article_meta_name' => 'required|string|createOnly|unique:article_metas,email_address,NULL,id,account_id,1',
        'content' => 'string',
        'property' => 'string'
    ];

    public function getValidationRules()
    {
        $metaUniqueRule = 'unique:article_metas,article_meta_name,NULL,NULL,article_id,'.$this->article_id;

        return [
            'article_id' => 'uuid|createOnly',
            'article_meta_name' => 'required|string|createOnly|'.$metaUniqueRule,
            'content' => 'string',
            'property' => 'string'
        ];
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'article_id');
    }

}