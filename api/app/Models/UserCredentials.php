<?php namespace App\Models;

class UserCredentials extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'user_credentials';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_credential_id', 'password'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    protected $primaryKey = 'user_credential_id';

    /**
     * Get the access route for the entity.
     *
     * @return string
     */
    public function entityRoute()
    {
        return '/user/{userId}/credentials'; //@todo make this route match work
    }

}