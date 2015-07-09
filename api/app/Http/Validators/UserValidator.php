<?php namespace App\Http\Validators;

use App\Services\Validator;

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
        'timezone_identifier' => 'timezone',
        'user_type' => 'string|in:public,admin'
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
