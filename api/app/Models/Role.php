<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use App\Models\Relations\RolePermissionRelation;
use Spira\Core\Model\Model\BaseModel;

/**
 * Class Role.
 *
 * @property string $key name of the current role
 */
class Role extends BaseModel
{
    const SUPER_ADMIN_ROLE = 'superAdmin';
    const ADMIN_ROLE = 'admin';
    const USER_ROLE = 'user';
    const TEST_ROLE = 'testrole';

    public static $roles = [
        self::ADMIN_ROLE,
        self::SUPER_ADMIN_ROLE,
        self::USER_ROLE,
        self::TEST_ROLE,
    ];

    public $exists = true;

    protected $primaryKey = 'key';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key', 'description', 'is_default'];

    protected static $validationRules = [
        'key' => 'required|rbac_role_exists',
    ];

    public function permissions()
    {
        return new RolePermissionRelation($this->key);
    }

    public static function findOrNew($id, $columns = ['*'])
    {
        return new static(['key' => $id]);
    }

    public function isDirty($attributes = null)
    {
        return false;
    }

    public function newCollection(array $models = [])
    {
        return new RoleCollection($models);
    }
}
