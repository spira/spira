<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Request;
use Tymon\JWTAuth\JWTAuth;

/**
 * Class AuthToken.
 */
class AuthToken extends Model
{
    /**
     * JWT Auth.
     *
     * @var JWTAuth
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
    protected $appends = ['decoded_token_body'];

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
     * @param array $attributes
     *
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
     * @param string $token
     *
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
     * @param mixed $attr
     *
     * @return array
     */
    public function getUserAttribute($attr)
    {
        return $attr ?: $this->jwtAuth->toUser($this->token)->toArray();
    }

    /**
     * Get the AUD attribute.
     *
     * @return string
     */
    public function getAudAttribute()
    {
        return str_replace('api.', '', Request::getHttpHost());
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
}