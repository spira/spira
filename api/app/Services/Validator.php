<?php namespace App\Services;

use Illuminate\Container\Container as App;

class Validator
{
    /**
     * The application instance.
     *
     * @var Illuminate\Container\Container
     */
    protected $app;

    /**
     * Validator.
     *
     * @var \Illuminate\Validation\Factory
     */
    protected $validator;

    /**
     * Data to validate.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Validation errors.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Assign dependencies.
     *
     * @param  Illuminate\Container\Container  $app
     * @return  void
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->validator = $this->app->make('validator');

        $this->registerValidateFloat();
        $this->registerValidateUuid();
    }

    /**
     * Validation rules.
     *
     * @return void
     */
    public function rules()
    {
        return [];
    }

    /**
     * Set custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'float' => 'The :attribute must be a float.'
        ];
    }

    /**
     * Add data to validate against.
     *
     * @param  array  $data
     * @return $this
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Test if validation passes.
     *
     * @return bool
     */
    public function passes()
    {
        $validator = $this->validator->make(
            $this->data,
            $this->rules(),
            $this->messages()
        );

        if ($validator->fails()) {
            $this->errors = $validator->messages();

            return false;
        }

        return true;
    }

    /**
     * Retrieve validation errors.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Register custom validation rule for float.
     *
     * @return void
     */
    protected function registerValidateFloat()
    {
        $this->validator->extend('float', function($attribute, $value, $parameters)
        {
            return is_float($value + 0);
        });
    }

    /**
     * Register custom validation rule for UUID strings.
     *
     * @return void
     */
    protected function registerValidateUuid()
    {
        $this->validator->extend('uuid', function($attribute, $value, $parameters)
        {
            return preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', $value);
        });
    }
}
