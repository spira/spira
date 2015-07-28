<?php namespace App\Models;

use App;
use Tymon\JWTAuth\Token;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = ['token'];

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
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->jwtAuth = App::make('Tymon\JWTAuth\JWTAuth');

        parent::__construct($attributes);
    }

    /**
     * Get the decoded token body attribute.
     *
     * @return array
     */
    public function getDecodedTokenBodyAttribute()
    {
        $token = new Token($this->token);
        $decoded = $this->jwtAuth->decode($token)->toArray();

        // For the api doc, we can have a mocked user, that has been injected
        // here from the model factory. If that is the case, replace the _user
        // key with the injected user
        if ($this->user) {
            $decoded['_user'] = $this->user;
        }

        return $decoded;
    }
}
