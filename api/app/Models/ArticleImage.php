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

}
