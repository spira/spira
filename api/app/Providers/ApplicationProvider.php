<?php


namespace App\Providers;

use Illuminate\Validation\Factory;
use App\Services\Validator;
use Spira\Core\Providers\AppServiceProvider;

class ApplicationProvider extends AppServiceProvider
{
    public function boot()
    {
        parent::boot();
        $spiraMessages = [
            'rbac_role_exists'     => 'The :attribute must be an existing Rbac role'
        ];

        $this->app->extend('validator', function (Factory $validator) use ($spiraMessages) {
            $validator->resolver(function ($translator, $data, $rules, $messages, $customAttributes) use ($spiraMessages) {
                return new Validator($translator, $data, $rules, array_merge($messages, $spiraMessages), $customAttributes);
            });

            return $validator;
        });
    }
}