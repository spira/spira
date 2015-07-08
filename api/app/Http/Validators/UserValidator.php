<?php namespace App\Http\Validators;

use App\Services\Validator;

class UserValidator extends Validator
{
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
