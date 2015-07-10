<?php namespace App\Http\Validators;

use App\Models\User;
use App\Services\Validator;
use Illuminate\Container\Container as App;

class UserValidator extends Validator
{
    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [
        'email' => 'required|email',
        'first_name' => 'string',
        'last_name' => 'string',
        'phone' => 'string',
        'mobile' => 'string',
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
     * @param  App  $app
     * @return void
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        // Get the possible user types and add them to the rules array
        $types = implode(',', User::$userTypes);
        $this->rules = array_merge($this->rules, ['user_type' => 'string|in:'.$types]);
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
}
