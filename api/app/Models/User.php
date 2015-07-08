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
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'reset_token',
        'phone',
        'mobile',
        'timezone_identifier'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['reset_token'];

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
     * Get the credentials associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function userCredentials()
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
        return $this->userCredentials ? $this->userCredentials->password : false;
    }
}
