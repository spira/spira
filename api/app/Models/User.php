<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Rhumsaa\Uuid\Uuid;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Spira\Core\Model\Model\IndexedModel;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Relations\UserRoleRelation;
use Spira\Core\Model\Collection\Collection;
use Spira\Auth\User\SocialiteAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * Class User.
 *
 * * lazy load
 * @property Role[]|Collection $roles
 *
 * * scopes
 * @method static Builder hasRoles(array $roleKeys) modifies query to return only users who has particular role
 */
class User extends IndexedModel implements AuthenticatableContract, SocialiteAuthenticatable
{
    use Authenticatable;

    /**
     * Login/email confirm token time to live in minutes.
     *
     * @var int
     */
    protected $token_ttl = 1440;

    protected $authMethod;

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
        'avatar_img_url',
        'email',
        'region_code',
        'avatar_img_id',
    ];

    /**
     * Model validation.
     * @param null $entityId
     * @return array
     */
    public static function getValidationRules($entityId = null, array $requestEntity = [])
    {
        return [
            'user_id' => 'required|uuid',
            'username' => 'required|between:3,50|alpha_dash_space|unique:users,username,'.$entityId.',user_id',
            'email' => 'required|email|unique:users,email,'.$entityId.',user_id',
            'email_confirmed' => 'date',
            'first_name' => 'string',
            'last_name' => 'string',
            'country' => 'country',
            'region_code' => 'string|supported_region',
            'timezone_identifier' => 'timezone',
            'avatar_img_id' => 'uuid',
        ];
    }

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
        self::CREATED_AT => 'datetime',
        self::UPDATED_AT => 'datetime',
    ];

    protected $mappingProperties = [
        'user_id' => [
            'type' => 'string',
            'index' => 'no',
        ],
        'timezone_identifier' => [
            'type' => 'string',
            'index' => 'no',
        ],
        'avatar_img_url' => [
            'type' => 'string',
            'index' => 'no',
        ],
        'country' => [
            'type' => 'string',
            'index' => 'no'
        ],
        'created_at' => [
            'type' => 'string',
            'index' => 'no'
        ],
        'avatar_img_id' => [
            'type' => 'string',
            'index' => 'no'
        ],
        'email_confirmed' => [
            'type' => 'string',
            'index' => 'no'
        ],
        'updated_at' => [
            'type' => 'string',
            'index' => 'no'
        ],
        'username' => [
            'type' => 'string',
            'index_analyzer' => 'autocomplete',
            'search_analyzer' => 'standard',
        ],
        'email' => [
            'type' => 'string',
            'index_analyzer' => 'autocomplete',
            'search_analyzer' => 'standard',
        ],
        'first_name' => [
            'type' => 'string',
            'index_analyzer' => 'autocomplete',
            'search_analyzer' => 'standard',
        ],
        'last_name' => [
            'type' => 'string',
            'index_analyzer' => 'autocomplete',
            'search_analyzer' => 'standard',
        ],
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
     * Get the user role objects.
     *
     * @return UserRoleRelation|\Illuminate\Database\Eloquent\Builder
     */
    public function roles()
    {
        return new UserRoleRelation((new Role())->newQuery(), $this, 'roles', 'user_id', 'role_key', 'roles');
    }

    public function bookmarkedArticles()
    {
        return $this->morphedByMany(Article::class, 'bookmarkable', 'bookmarks');
    }

    public function ratedArticles()
    {
        return $this->morphedByMany(Article::class, 'rateable', 'ratings')->withPivot(['rating_value']);
    }

    /**
     * @return \DateTimeZone|null
     */
    public function getTimeZone()
    {
        if ($this->timezone_identifier) {
            return new \DateTimeZone($this->timezone_identifier);
        }

        return;
    }

    /**
     * Get the user's uploaded avatar image if they have one.
     *
     * @return HasOne|\Illuminate\Database\Eloquent\Builder
     */
    public function uploadedAvatar()
    {
        return $this->hasOne(Image::class, 'image_id', 'avatar_img_id');
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
        if (! $this->userCredential) {
            return '';
        }

        return $this->userCredential->password;
    }

    /**
     * @param string $id user_id or email
     * @return AbstractPost
     * @throws ModelNotFoundException
     */
    public function findByIdentifier($id)
    {
        //if the id is a uuid, try that or fail.
        if (Uuid::isValid($id)) {
            return static::findOrFail($id);
        }

        return $this->findByEmail($id);
    }

    /**
     * Get a user by single use login token.
     *
     * @param string $token
     *
     * @return User|null
     */
    public function findByLoginToken($token)
    {
        if ($id = Cache::pull('login_token_'.$token)) {
            $user = $this->findOrFail($id);

            return $user;
        }

        return;
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

        Cache::put('email_confirmation_'.$token, $newEmail, $this->token_ttl);

        Cache::put('email_change_'.$newEmail, $oldEmail, $this->token_ttl);

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
        $newEmail = Cache::pull('email_confirmation_'.$token, false);

        if ($newEmail) {
            Cache::forget('email_change_'.$newEmail);
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
        return Cache::get('email_change_'.$newEmail, false); // Return false on cache miss
    }

    /**
     * @param string $method
     * @return void
     */
    public function setCurrentAuthMethod($method)
    {
        $this->authMethod = $method;
    }

    /**
     * @return string
     */
    public function getCurrentAuthMethod()
    {
        return $this->authMethod;
    }

    /**
     * @param Builder|\Illuminate\Database\Query\Builder $query
     * @param array $roleKeys
     * @return Builder
     */
    public function scopeHasRoles(Builder $query, array $roleKeys = [])
    {
        return $query->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
            ->whereIn('roles.role_key', $roleKeys);
    }
}
