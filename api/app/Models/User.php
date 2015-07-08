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
}
