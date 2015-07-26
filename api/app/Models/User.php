<?php namespace App\Models;

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
     * Model validation.
     *
     * @var array
     */
    protected $validationRules = [
        'user_id' => 'uuid',
        'email' => 'required|email',
        'first_name' => 'string',
        'last_name' => 'string',
        'phone' => 'string',
        'mobile' => 'string',
        'country' => 'country',
        'timezone_identifier' => 'timezone'
    ];

    /**
     * Get the credentials associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function userCredential()
    {
        return $this->hasOne(UserCredential::class);
    }

    /**
     * Set the user's credentials.
     *
     * @param  UserCredential  $credential
     * @return $this
     */
    public function setCredential(UserCredential $credential)
    {
        $this->userCredential()->save($credential);

        return $this;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        // If no user credential is associated with the user, just return an
        // empty string which will trigger a ValidationException during
        // password_verify()
        if (!$this->userCredential) {
            return '';
        }

        return $this->userCredential->password;
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
