<?php namespace App\Models;

class UserProfile extends BaseModel
{
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
        'phone',
        'mobile',
        'avatar_img_url',
        'dob'
    ];

    /**
     * Model validation.
     *
     * @var array
     */
    protected $validationRules = [
        'phone' => 'string',
        'mobile' => 'string',
        'dob' => 'date',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'dob' => 'date',
    ];
}