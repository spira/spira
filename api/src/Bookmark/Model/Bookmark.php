<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Bookmark\Model;

use Spira\Model\Model\BaseModel;

class Bookmark extends BaseModel
{
    public $table = 'bookmarks';

    protected $primaryKey = 'bookmark_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id'];

    protected static $validationRules = [
        'user_id' => 'required|uuid|exists:users,user_id',
    ];

    public function bookmarkable()
    {
        return $this->morphTo();
    }
}
