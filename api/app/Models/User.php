<?php namespace App\Models;

use BeatSwitch\Lock\LockAware;
use BeatSwitch\Lock\Callers\Caller;
use Illuminate\Auth\Authenticatable;
use App\Extensions\Lock\UserOwnership;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends BaseModel implements AuthenticatableContract, Caller, UserOwnership
{
    use Authenticatable, LockAware;

    /**
     * Login token time to live in minutes.
     *
     * @var int
     */
    protected $login_token_ttl = 1440;

    /**
     * Cache repository.
     *
     * @var Cache
     */
    protected $cache;

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
     * Make an email confirmation token for a user.
     *
     * @param  string  $email
     * @param  Cache   $cache
     *
     * @return string
     */
    public function makeConfirmationToken($email, Cache $cache)
    {
        $token = hash_hmac('sha256', str_random(40), str_random(40));
        $cache->put('email_confirmation_'.$token, $email, 1440);
        return $token;
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
        if ($id = $this->getCache()->pull('login_token_'.$token)) {
            $user = $this->findOrFail($id);

            return $user;
        }

        return null;
    }

    /**
     * @todo remove this hack
     * @return Cache
     */
    protected function getCache()
    {
        if (!$this->cache) {
            $this->cache = \App::make(Cache::class);
        }

        return $this->cache;
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
        $this->getCache()->put('login_token_'.$token, $user->user_id, $this->login_token_ttl);

        return $token;
    }
}
