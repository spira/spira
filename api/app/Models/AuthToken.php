<?php namespace App\Models;

use Request;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Database\Eloquent\Model;
/**
 * Class AuthToken
 * @package App\Models
 *
 * Note this model does not have an associated database table as it is an abstract
 * data model with generated token data. It needs to extend eloquent as it needs to
 * join on the User model
 *
 */
class AuthToken extends BaseModel
{
    /**
     * JWT Auth
     *
     * @var Tymon\JWTAuth\JWTAuth
     */
    protected $jwtAuth;

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
    protected $fillable = ['token', 'decoded_token_body'];

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
        'nbf' => 'dateTime',
        'iat' => 'dateTime',
        'exp' => 'dateTime',
    ];

    /**
     * Assign dependencies and initialize attributes.
     *
     * @param  string  $token
     * @param  JWTAuth  $jwtAuth
     * @return void
     */
    public function __construct($token, JWTAuth $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
        $decoded_token_body = [];

        parent::__construct(compact('token', 'decoded_token_body'));
    }

    /**
     * Get the decoded token body attribute.
     *
     * @return array
     */
    public function getDecodedTokenBodyAttribute()
    {
        // Prepare the attributes, and attribute order
        $body = array_fill_keys(
            ['iss', 'aud', 'sub', 'nbf', 'iat', 'exp', 'jti', '#user'],
            null
        );

        $payload = $this->jwtAuth->getPayload($this->token)->toArray();
        $body = array_merge($body, $payload);

        // Set the host attributes
        $body['iss'] = $body['aud'] = Request::getHttpHost();

        return $body;
    }

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
