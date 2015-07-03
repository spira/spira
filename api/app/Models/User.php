<?php namespace App\Models;

class User extends BaseModel {

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
    protected $hidden = ['password', 'reset_token'];

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

}