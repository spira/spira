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
        'email' => 'required|string|email',
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
}
