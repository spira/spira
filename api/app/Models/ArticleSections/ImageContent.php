<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\ArticleSections;


use Spira\Model\Model\VirtualModel;

class ImageContent extends VirtualModel
{

    const CONTENT_TYPE = 'image';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'images',
    ];

    protected static $validationRules = [
        'images' => 'required|array',
    ];


}
