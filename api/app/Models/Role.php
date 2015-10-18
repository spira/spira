<?php


namespace App\Models;


use Spira\Model\Model\BaseModel;

/**
 * Class Role
 *
 * @property string $role_name name of the current role
 *
 */
class Role extends BaseModel
{
    public $table = 'roles';

    protected $primaryKey = 'role_name';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','role_name'];
}