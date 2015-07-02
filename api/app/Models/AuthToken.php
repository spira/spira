<?php namespace App\Models;

use Request;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuthToken
 * @package App\Models
 *
 * Note this model does not have an associated database table as it is an abstract
 * data model with generated token data.
 */
class AuthToken extends Model
{
    /**
     * JWT Auth
     *
     * @var Tymon\JWTAuth\JWTAuth
     */
    protected $jwtAuth;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['token', 'iss', 'aud', 'sub', 'nbf', 'iat', 'exp', 'jti', 'user'];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['token', 'decoded_token_body'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public $appends = ['decoded_token_body'];

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
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->jwtAuth = \App::make('Tymon\JWTAuth\JWTAuth');

        $attributes = $attributes + $this->claimsToAttributes($attributes['token']);

        parent::__construct($attributes);
    }

    /**
     * Convert the payload claims to model attributes.
     *
     * @param  string $token
     * @return array
     */
    protected function claimsToAttributes($token)
    {
        // Prepare the attributes, and attribute order
        $body = array_fill_keys(array_except($this->fillable, 'token'), null);

        $payload = $this->jwtAuth->getPayload($token)->toArray();
        $body = array_merge($body, $payload);

        return $body;
    }

    /**
     * Get the decoded token body attribute.
     *
     * @return array
     */
    public function getDecodedTokenBodyAttribute()
    {
        foreach (array_keys(array_except($this->getAttributes(), ['token', 'user'])) as $key) {
            $body[$key] = $this->getAttribute($key);
        }

        // Map the user key with #
        $body['#user'] = $this->user;

        return $body;
    }

    /**
     * Get the user attribute.
     *
     * @return array
     */
    public function getUserAttribute()
    {
        return $this->jwtAuth->toUser($this->token)->toArray();
    }

    /**
     * Get the AUD attribute.
     *
     * @return string
     */
    public function getAudAttribute()
    {
        return Request::getHttpHost();
    }

    /**
     * Get the ISS attribute.
     *
     * @return string
     */
    public function getIssAttribute()
    {
        return Request::getHttpHost();
    }

    public function getToken($tokenBody){

        $header = [
            'alg' => "HS256",
            'typ' => "JWT"
        ];

        $token = json_encode($header) . $tokenBody;
    }
}
