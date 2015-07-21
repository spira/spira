<?php

namespace App\Models;

use BeatSwitch\Lock\LockAware;
use BeatSwitch\Lock\Callers\Caller;
use App\Extensions\Lock\UserOwnership;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends BaseModel implements AuthenticatableContract, Caller, UserOwnership
{
    use Authenticatable, LockAware;

    const USER_TYPE_ADMIN = 'admin';
    const USER_TYPE_GUEST = 'guest';
    public static $userTypes = [self::USER_TYPE_ADMIN, self::USER_TYPE_GUEST];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'users';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'timezone_identifier',
        'user_type'
    ];

    /**
     * Get the access route for the entity.
     *
     * @return string
     */
    public function entityRoute()
    {
        return '/users';
    }

    /**
     * Get the credentials associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function userCredential()
    {
        return $this->hasOne('App\Models\UserCredential');
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->userCredential ? $this->userCredential->password : false;
    }

    /**
     * The type of caller for lock permission.
     *
     * @return string
     */
    public function getCallerType()
    {
        return 'users';
    }

    /**
     * The unique ID to identify the caller with for lock permission.
     *
     * @return string
     */
    public function getCallerId()
    {
        return $this->user_id;
    }

    /**
     * The caller's roles for lock permission.
     *
     * @return array
     */
    public function getCallerRoles()
    {
        return [$this->user_type];
    }

    /**
     * Check if the user is owns the entity.
     *
     * @param  \App\Models\User  $user
     * @param  string            $entityId
     * @return bool
     */
    public static function userIsOwner($user, $entityId)
    {
        return $user->user_id == $entityId;
    }
}