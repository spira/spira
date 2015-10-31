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
    const SUPER_ADMIN_ROLE = 'superAdmin';
    const ADMIN_ROLE = 'admin';
    const USER_ROLE = 'user';

    public static $roles = [
        self::ADMIN_ROLE,
        self::SUPER_ADMIN_ROLE,
        self::USER_ROLE
    ];

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
