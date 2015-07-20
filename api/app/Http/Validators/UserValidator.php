<?php namespace App\Http\Validators;

use App\Models\User;
use App\Services\Validator;
use Illuminate\Http\Request;
use Illuminate\Container\Container as App;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class UserValidator extends Validator
{
    /**
     * Request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Cache repository.
     *
     * @var CacheRepository
     */
    protected $cache;

    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [
        'email' => 'required|email',
        'email_confirmed' => 'date|email_confirmation_token',
        'first_name' => 'string',
        'last_name' => 'string',
        'phone' => 'string',
        'mobile' => 'string',
        'country' => 'country',
        'timezone_identifier' => 'timezone'
    ];

    /**
     * Model being validated.
     *
     * @var string
     */
    protected function model()
    {
        return 'App\Models\User';
    }

    /**
     * Dynamically adjust the rules array during construction.
     *
     * @param  App              $app
     * @param  Request          $request
     * @param  CacheRepository  $cache
     * @return void
     */
    public function __construct(App $app, Request $request, CacheRepository $cache)
    {
        parent::__construct($app);

        // Get the possible user types and add them to the rules array
        $types = implode(',', User::$userTypes);
        $this->rules = array_merge($this->rules, ['user_type' => 'string|in:'.$types]);

        $this->request = $request;
        $this->cache = $cache;

        $this->registerEmailConfirmationToken();
    }

    /**
     * Validation rules.
     *
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }

    /**
     * Modify the rules for put operations.
     *
     * In the case of users, replacement of the entity with a put operation is
     * not permitted, so the rules is modified according to that.
     *
     * @return $this
     */
    public function put()
    {
        $this->rules = array_add($this->rules, $this->getKey(), 'required|uuid|unique:'.$this->getTable().','.$this->getKey());

        return $this;
    }

    /**
     * Register custom validation rule for email confirmation token.
     *
     * @return void
     */
    protected function registerEmailConfirmationToken()
    {
        $this->validator->extend('email_confirmation_token', function ($attribute, $value, $parameters) {

            $token = $this->request->headers->get('email-confirm-token');

            if ($email = $this->cache->pull('email_confirmation_'.$token)) {
                return true;
            }
            return false;
        });
    }
}
