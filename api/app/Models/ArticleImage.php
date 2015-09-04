<?php

/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 27.08.15
 * Time: 12:07.
 */

namespace App\Models;

use Spira\Model\Model\BaseModel;

class ArticleImage extends BaseModel
{
    public $table = 'article_image';

    protected $primaryKey = 'article_image_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['article_image_id','image_id','article_id', 'image_type', 'position', 'alt', 'title'];

    protected static $validationRules = [
        'article_image_id' => 'required|uuid',
        'image_id' => 'required|uuid',
        'article_id' => 'required|uuid',
        'position' => 'numeric',
        'image_type' => 'string',
        'alt' => 'string|max:255',
        'title' => 'string|max:255',
    ];

    public function image()
    {
        return $this->hasOne(Image::class, 'image_id', 'image_id');
    }

    public function article()
    {
        return $this->hasOne(Article::class);
    }
}
