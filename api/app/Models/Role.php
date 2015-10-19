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

/**
 * Class Role.
 *
 * @property string role_key name of the current role
 */
class Role extends BaseModel
{
    public $table = 'roles';

    protected $primaryKey = 'role_key';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','role_key'];
}
