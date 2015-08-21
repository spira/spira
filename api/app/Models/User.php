<?php namespace App\Models;

use BeatSwitch\Lock\LockAware;
use BeatSwitch\Lock\Callers\Caller;
use Illuminate\Auth\Authenticatable;
use App\Extensions\Lock\UserOwnership;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Spira\Model\Model\IndexedModel;

class User extends IndexedModel implements AuthenticatableContract, Caller, UserOwnership
{

    use Authenticatable, LockAware;

    const USER_TYPE_ADMIN = 'admin';
    const USER_TYPE_GUEST = 'guest';
    public static $userTypes = [self::USER_TYPE_ADMIN, self::USER_TYPE_GUEST];
    
    /**
     * Login/email confirm token time to live in minutes.
     *
     * @var int
     */
    protected $token_ttl = 1440;

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
    protected static $validationRules = [
        'user_id' => 'required|uuid',
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
     * @return HasOne
     */
    public function userCredential()
    {
        return $this->hasOne(UserCredential::class);
    }

    /**
     * Get the profile associated with the user.
     *
     * @return HasOne
     */
    public function userProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the social logins associated with the user.
     *
     * @return HasMany|\Illuminate\Database\Eloquent\Builder
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
     * Get a user by single use login token.
     *
     * @param string $token
     *
     * @return mixed
     */
    public function findByLoginToken($token)
    {
        if ($id = Cache::pull('login_token_'.$token)) {
            $user = $this->findOrFail($id);

            return $user;
        }

        return null;
    }

    /**
     * Get a user by their email.
     *
     * @param $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        return $this->where('email', '=', $email)->firstOrFail();
    }

    /**
     * Make a single use login token for a user.
     *
     * @param string $id
     *
     * @return string
     */
    public function makeLoginToken($id)
    {
        $user = $this->findOrFail($id);

        $token = hash_hmac('sha256', str_random(40), str_random(40));
        Cache::put('login_token_'.$token, $user->user_id, $this->token_ttl);

        return $token;
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

        Cache::put('email_confirmation_' . $token, $newEmail, $this->token_ttl);

        Cache::put('email_change_' . $newEmail, $oldEmail, $this->token_ttl);

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
