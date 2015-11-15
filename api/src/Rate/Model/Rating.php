<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rate\Model;

use Spira\Model\Model\BaseModel;

class Rating extends BaseModel
{
    public $table = 'ratings';

    protected $primaryKey = 'rating_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['rating_value', 'user_id'];

    protected static $validationRules = [
        'rating_value' => 'required|integer',
        'user_id' => 'required|uuid|exists:users,user_id',
    ];

    public function rateable()
    {
        return $this->morphTo();
    }
}
