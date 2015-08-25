<?php

namespace App\Models;

use Spira\Model\Model\BaseModel;

class ArticleComment extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'article_comment_id',
        'body',
        'created_at',
        'author_name',
        'author_email',
        'author_photo'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];
}
