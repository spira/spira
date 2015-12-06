<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Spira\Core\Model\Model\BaseModel;

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
    protected $fillable = ['image_id', 'version', 'format', 'folder', 'alt', 'title'];

    protected static $validationRules = [
        'image_id' => 'required|uuid',
        'version' => 'required|numeric',
        'format' => 'required|string',
        'folder' => 'string|max:10',
        'alt' => 'required|string|max:255',
        'title' => 'string|max:255',
    ];
}
