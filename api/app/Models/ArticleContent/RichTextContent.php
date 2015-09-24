<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\ArticleContent;


use Spira\Model\Model\VirtualModel;

class RichTextContent extends VirtualModel
{

    const CONTENT_TYPE = 'rich_text';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'body',
    ];

    protected static $validationRules = [
        'body' => 'required|text',
    ];


}
