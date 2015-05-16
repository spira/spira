<?php namespace App\Models;

class User extends BaseModel {
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
    protected $fillable = ['user_id', 'first_name', 'last_name', 'email', 'password', 'reset_token'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'reset_token'];
}