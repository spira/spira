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

class Permission extends BaseModel
{
    protected $primaryKey = 'key';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key', 'description'];
}
