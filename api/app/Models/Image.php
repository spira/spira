<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 25.08.15
 * Time: 2:03
 */

namespace App\Models;

use Spira\Model\Model\BaseModel;

class Image extends BaseModel
{
    public $table = 'images';

    protected $primaryKey = 'image_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['image_id','version', 'format', 'folder', 'alt', 'title'];

    protected static $validationRules = [
        'image_id' => 'required|uuid',
        'version' => 'required|numeric',
        'format' => 'required|string',
        'folder' => 'string|max:10',
        'alt' => 'required|string|max:255',
        'title' => 'string|max:255',
    ];
}
