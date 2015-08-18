<?php namespace App\Models;

use BeatSwitch\Lock\LockAware;
use BeatSwitch\Lock\Callers\Caller;
use Illuminate\Auth\Authenticatable;
use App\Extensions\Lock\UserOwnership;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\Cache;

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
        'username',
        'first_name',
        'last_name',
        'email_confirmed',
        'timezone_identifier',
        'user_type',
        'avatar_img_url',
        'email',
    ];

    /**
     * Model validation.
     *
     * @var array
     */
    protected $validationRules = [
        'user_id' => 'uuid|createOnly',
        'username' => 'required|between:3,50|alpha_dash_space',
        'email' => 'required|email',
        'email_confirmed' => 'date',
        'first_name' => 'string',
        'last_name' => 'string',
        'country' => 'country',
        'timezone_identifier' => 'timezone',
    ];

    /**
     * The attributes that should be mutated to datetimes.
     *
     * @var array
     */
    protected $dates = ['email_confirmed'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_confirmed' => 'datetime',
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
     * Get the profile associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function userProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the social logins associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function socialLogins()
    {
        return $this->hasMany(SocialLogin::class);
    }

    /**
     *
     * @todo Replace these two methods with the hasMany relationship for roles
     *       when implementing. For now they "simulate" the relationship so
     *       functionality accessing roles will get a similar dataset as when
     *       the relation is implemented
     */
    public function roles()
    {
        return new \Illuminate\Support\Collection([['name' => $this->user_type]]);
    }
    public function getRolesAttribute()
    {
        return $this->roles();
    }

    /**
     * Set the user's credentials.
     *
     * @param  UserCredential  $credential
     *
     * @return $this
     */
    public function setCredential(UserCredential $credential)
    {
        $this->userCredential()->save($credential);

        return $this;
    }

    /**
     * Set the user's profile.
     *
     * @param UserProfile $profile
     * @return $this
     *
     */
    public function setProfile(UserProfile $profile)
    {
        $this->userProfile()->save($profile);

        return $this;
    }

    /**
     * Add or update a social login for the user.
     *
     * @param  SocialLogin  $socialLogin
     *
     * @return $this
     */
    public function addSocialLogin(SocialLogin $socialLogin)
    {
        $login = $this->socialLogins()->where('provider', $socialLogin->provider)->first();

        if ($login) {
            $login->fill($socialLogin->toArray())->save();
        } else {
            $this->socialLogins()->save($socialLogin);
        }

        return $this;
    }

    /**
     * Accessor to get full name attribute for the user.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return sprintf('%s %s', $this->first_name, $this->last_name);
    }

    /**
     * Scope a query by username.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string                                 $username
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUsername($query, $username)
    {
        return $query->where('username', 'ilike', $username);
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
     *
     * @return bool
     */
    public static function userIsOwner($user, $entityId)
    {
        return $user->user_id == $entityId;
    }

    /**
     * Create an email confirmation token for a user.
     *
     * @param $newEmail
     * @param $oldEmail
     * @return string
     */
    public function createEmailConfirmToken($newEmail, $oldEmail)
    {
        $token = hash_hmac('sha256', str_random(40), str_random(40));

        Cache::put('email_confirmation_' . $token, $newEmail, 1440);

        Cache::put('email_change_' . $newEmail, $oldEmail, 1440);

        return $token;
    }

    /**
     * Get an email address from supplied token.
     *
     * @param $token
     * @return mixed
     */
    public function getEmailFromToken($token)
    {
        $newEmail = Cache::pull('email_confirmation_' . $token, false);

        if ($newEmail) {
            Cache::forget('email_change_' . $newEmail);
        }

        return $newEmail;
    }

    /**
     * Check to see if the user has made a change email request. Return the current email associated with the new email.
     *
     * @param $newEmail
     * @return mixed
     */
    public static function findCurrentEmail($newEmail)
    {
        return Cache::get('email_change_' . $newEmail, false); // Return false on cache miss
    }
}
