<?php namespace App\Models;

use Hash;

class UserCredential extends BaseModel
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_credential_id';

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

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Model validation.
     *
     * @var array
     */
    protected $validationRules = [
        'user_credential_id' => 'uuid',
        'password' => 'required|string',
    ];

    /**
     * Encrypt the user's password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
