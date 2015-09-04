<?php

namespace App\Models;

use Spira\Model\Model\BaseModel;

class UserProfile extends BaseModel
{
    /**
     * The character length of the 'about' field.
     */
    const ABOUT_LENGTH = 120;

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
        'dob',
        'gender',
        'about',
        'facebook',
        'twitter',
        'pinterest',
        'instagram',
        'website',
    ];

    /**
     * Model validation.
     *
     * @var array
     */
    protected static $validationRules = [
        'phone' => 'string',
        'mobile' => 'string',
        'dob' => 'date',
        'gender' => 'in:M,F,N/A',
        'about' => 'string',
        'facebook' => 'string',
        'twitter' => 'string',
        'pinterest' => 'string',
        'instagram' => 'string',
        'website' => 'string',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'dob' => 'date',
        self::CREATED_AT => 'datetime',
        self::UPDATED_AT => 'datetime',
    ];
}
