<?php namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends BaseModel implements AuthenticatableContract
{
    use Authenticatable;

    const USER_TYPE_ADMIN = 'admin';
    const USER_TYPE_PUBLIC = 'public';
    public static $userTypes = [self::USER_TYPE_ADMIN, self::USER_TYPE_PUBLIC];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'first_name', 'last_name', 'email', 'password', 'reset_token', 'phone', 'mobile'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'reset_token', 'login_token'];

    protected $primaryKey = 'user_id';

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
     * Scope a query to find a user by login_token.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string                                 $token
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLoginToken($query, $token)
    {
        return $query->where('login_token', $token);
    }
}
