<?php namespace App\Models;

/**
 * Class AuthToken
 * @package App\Models
 *
 * Note this model does not have an associated database table as it is an abstract
 * data model with generated token data. It needs to extend eloquent as it needs to
 * join on the User model
 *
 */
class AuthToken extends BaseModel {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $primaryKey = 'id';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'nbf' => 'integer',
        'iat' => 'integer',
        'exp' => 'integer',
    ];

    /**
     * Get the access route for the entity.
     *
     * @return string
     */
    public function entityRoute()
    {
        return '/auth/jwt/login';
    }

    public $appends = []; //don't show _self in model

    public function getToken($tokenBody){

        $header = [
            'alg' => "HS256",
            'typ' => "JWT"
        ];

        $token = json_encode($header) . $tokenBody;



    }

}